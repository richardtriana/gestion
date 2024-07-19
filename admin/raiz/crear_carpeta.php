<?php
// archivo: crear_carpeta.php

include '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ubicacion = $_POST['ubicacion'];
    $nombreCarpeta = $_POST['nombreCarpeta'];

    // Asegurarse de que la ubicación y el nombre de la carpeta no estén vacíos
    if (empty($ubicacion) || empty($nombreCarpeta)) {
        $mensaje = "La ubicación y el nombre de la carpeta no pueden estar vacíos";
        header("Location: raiz.php?mensaje=" . urlencode($mensaje));
        exit;
    }

    // Crear el directorio en la ubicación seleccionada con el nombre especificado
    $rutaCompleta = $ubicacion . DIRECTORY_SEPARATOR . $nombreCarpeta;
    if (!is_dir($rutaCompleta)) {
        if (mkdir($rutaCompleta, 0777, true)) {
            $mensaje = "Directorio '$nombreCarpeta' creado con éxito en $ubicacion";

            // Insertar los datos en la tabla 'raiz'
            $sql = "INSERT INTO raiz (ubicacion, nombre_carpeta, ruta_completa) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $ubicacion, $nombreCarpeta, $rutaCompleta);
            if ($stmt->execute()) {
                $mensaje .= " y guardado en la base de datos.";
            } else {
                $mensaje .= " pero ocurrió un error al guardar en la base de datos.";
            }
        } else {
            $mensaje = "Error al crear el directorio '$nombreCarpeta'";
        }
    } else {
        $mensaje = "El directorio '$nombreCarpeta' ya existe en $ubicacion";
    }

    // Redirigir de vuelta a la página de gestión de carpetas con el mensaje de éxito
    header("Location: raiz.php?mensaje=" . urlencode($mensaje));
    exit;
}
?>
