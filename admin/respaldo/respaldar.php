<?php
include '../conexion.php';

header('Content-Type: application/json');

session_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

function update_status($message) {
    $_SESSION['backup_status'] = $message;
}

function backup_database($ruta) {
    $dbname = 'gestion';
    $user = 'root';
    $password = '';
    $host = 'localhost';
    $mysqldump_path = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';

    $backup_file = $ruta . "/backup_db.sql";

    $command = "\"$mysqldump_path\" --user=$user --password=$password --host=$host $dbname > \"$backup_file\" 2>&1";
    $output = [];
    $result_code = 0;
    exec($command, $output, $result_code);

    if ($result_code === 0 && file_exists($backup_file)) {
        return $backup_file;
    } else {
        error_log('mysqldump error: ' . implode("\n", $output));
        return false;
    }
}

function create_zip($source, $destination) {
    if (!extension_loaded('zip') || !file_exists($source)) {
        error_log('La extensión ZIP no está cargada o la fuente no existe.');
        return false;
    }

    $zip = new ZipArchive();
    if (!$zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
        error_log('No se pudo abrir el archivo ZIP: ' . $destination);
        return false;
    }

    $source = realpath($source);
    if (is_dir($source)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($source) + 1);

                error_log("Agregando archivo: $filePath como $relativePath");
                if (!$zip->addFile($filePath, $relativePath)) {
                    error_log("Error al agregar archivo: $filePath");
                }
            }
        }
    } else if (is_file($source)) {
        error_log("Agregando archivo único: $source");
        if (!$zip->addFile($source, basename($source))) {
            error_log("Error al agregar archivo único: $source");
        }
    } else {
        error_log("El source no es un archivo ni un directorio: $source");
    }

    if ($zip->close()) {
        error_log('El archivo ZIP se creó correctamente: ' . $destination);
        return true;
    } else {
        error_log('No se pudo cerrar el archivo ZIP: ' . $destination);
        return false;
    }
}

function delete_directory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        if (!delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    return rmdir($dir);
}

function copy_directory($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                copy_directory($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

function backup_directory($ruta) {
    include '../conexion.php';

    $sql = "SELECT ruta_completa FROM raiz";
    $result = $conn->query($sql);
    $root_folder = '';
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $root_folder = $row['ruta_completa'];
    } else {
        error_log('No se encontró la carpeta raíz en la base de datos');
        return false;
    }

    copy_directory($root_folder, $ruta);
    return true;
}

function registrar_respaldo($conn, $estado, $inicio, $fin, $ruta) {
    $sql = "INSERT INTO respaldo (estado, fecha_inicio, fecha_fin, ruta) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $estado, $inicio, $fin, $ruta);
    $stmt->execute();
}

function handle_error($message) {
    update_status($message);
    error_log($message);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

function handle_exception($exception) {
    handle_error($exception->getMessage());
}

set_exception_handler('handle_exception');

$response = ['success' => false];
try {
    $inicio = date('Y-m-d H:i:s', time());

    $sql = "SELECT * FROM configuracion WHERE id = 1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $configuracion = $result->fetch_assoc();
        $ruta_respaldo = $configuracion['ruta_respaldo'];
        $dias_semana = explode(',', $configuracion['dias_semana']);
        $hora_configurada = $configuracion['hora_respaldo'];

        $dia_actual = date('l');
        $hora_actual = date('H:i:s');

        $dias_semana_traducidos = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado',
            'Sunday' => 'Domingo'
        ];

        $dia_actual_traducido = $dias_semana_traducidos[$dia_actual];

        if (in_array($dia_actual_traducido, $dias_semana) && $hora_actual >= $hora_configurada) {
            $ruta_completa = $ruta_respaldo . '/' . $dia_actual_traducido;
            if (file_exists($ruta_completa)) {
                update_status('Eliminando copia de seguridad anterior...');
                if (!delete_directory($ruta_completa)) {
                    handle_error('Error al eliminar la copia de seguridad anterior.');
                }
                if (!mkdir($ruta_completa, 0777, true)) {
                    handle_error('Error al crear el directorio de respaldo.');
                }
            } else {
                update_status('Creando directorio de respaldo...');
                if (!mkdir($ruta_completa, 0777, true)) {
                    handle_error('Error al crear el directorio de respaldo.');
                }
            }

            update_status('Respaldando base de datos...');
            $db_backup = backup_database($ruta_completa);
            if ($db_backup) {
                $response['success'] = true;
                $response['db_backup'] = $db_backup;
                update_status('Copia de seguridad de la base de datos completada...');
            } else {
                handle_error('Error al respaldar la base de datos.');
            }

            update_status('Respaldando archivos...');
            if (backup_directory($ruta_completa)) {
                $dir_backup = $ruta_completa;
                $zip_file = "$ruta_completa/backup_files.zip";
                update_status('Comprimiendo archivos...');
                if (create_zip($dir_backup, $zip_file)) {
                    // Eliminamos los archivos copiados, dejando solo el zip y la base de datos
                    $files = array_diff(scandir($dir_backup), ['.', '..', 'backup_db.sql', 'backup_files.zip']);
                    foreach ($files as $file) {
                        if (is_dir("$dir_backup/$file")) {
                            delete_directory("$dir_backup/$file");
                        } else {
                            unlink("$dir_backup/$file");
                        }
                    }
                    $response['success'] = true;
                    $response['dir_backup'] = $zip_file;
                    update_status('Copia de seguridad de los archivos completada...');
                } else {
                    handle_error('Error al crear el archivo ZIP.');
                }
            } else {
                handle_error('Error al respaldar la carpeta.');
            }

            $fin = date('Y-m-d H:i:s', time());
            $estado = isset($response['error']) ? 'incorrecto' : 'correcto';
            $ruta = isset($response['db_backup']) && isset($response['dir_backup']) ? $response['db_backup'] . ', ' . $response['dir_backup'] : '';
            registrar_respaldo($conn, $estado, $inicio, $fin, $ruta);
            $response['success'] = true;
            $response['message'] = 'Respaldo realizado con éxito.';
            update_status($response['message']);
        } else {
            handle_error('No se pudo iniciar el respaldo: día o hora no coinciden.');
        }
    } else {
        handle_error('No se encontró la configuración de respaldo');
    }

    echo json_encode($response);
    exit;
} catch (Exception $e) {
    handle_exception($e);
}
?>






































