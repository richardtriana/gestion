<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'admin/conexion.php';

$user_id = $_SESSION['user_id'];
$user_sql = "SELECT nombres FROM usuarios WHERE id = '$user_id'";
$user_result = $conn->query($user_sql);
$user_name = $user_result->fetch_assoc()['nombres'];

$dep_sql = "SELECT d.id, d.nombre, d.tamaño, d.ruta FROM dependencia d
            JOIN usuarios_dependencias ud ON d.id = ud.dependencia_id
            WHERE ud.usuario_id = '$user_id'";
$dep_result = $conn->query($dep_sql);

$dependencias = [];
while ($row = $dep_result->fetch_assoc()) {
    $dependencias[] = $row;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dependencia_id'])) {
    $_SESSION['dependencia_id'] = $_POST['dependencia_id'];
}

$dependencia_id = $_SESSION['dependencia_id'] ?? ($dependencias[0]['id'] ?? null);

if ($dependencia_id) {
    $dep_sql = "SELECT * FROM dependencia WHERE id='$dependencia_id'";
    $dep_result = $conn->query($dep_sql);
    $dependencia = $dep_result->fetch_assoc();

    if ($dependencia) {
        $base_dir = $dependencia['ruta'];
        $total_space = $dependencia['tamaño'] * 1024 * 1024 * 1024;

        function folderSize($dir) {
            $size = 0;
            foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
                $size += is_file($each) ? filesize($each) : folderSize($each);
            }
            return $size;
        }

        if (is_dir($base_dir)) {
            $used_space = folderSize($base_dir);
        }

        $free_space = $total_space - $used_space;
        $used_space_gb = round($used_space / (1024 * 1024 * 1024), 2);
        $total_space_gb = $dependencia['tamaño'];
        $used_percent = round(($used_space / $total_space) * 100, 2);
    } else {
        $base_dir = null;
        $total_space = 0;
        $used_space = 0;
        $free_space = 0;
        $used_space_gb = 0;
        $total_space_gb = 0;
        $used_percent = 0;
    }
} else {
    $base_dir = null;
    $total_space = 0;
    $used_space = 0;
    $free_space = 0;
    $used_space_gb = 0;
    $total_space_gb = 0;
    $used_percent = 0;
}

function getDirectoryStructure($dir, $current_dir) {
    $result = [];
    if ($dir && is_dir($dir)) {
        $cdir = scandir($dir);
        foreach ($cdir as $key => $value) {
            if (!in_array($value, [".", ".."])) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                    $path = $dir . DIRECTORY_SEPARATOR . $value;
                    $result[] = [
                        'type' => 'directory',
                        'name' => $value,
                        'path' => $path,
                        'children' => getDirectoryStructure($path, $current_dir),
                        'active' => strpos($current_dir, $path) === 0
                    ];
                }
            }
        }
    }
    return $result;
}

function renderDirectoryTree($structure) {
    echo '<ul>';
    foreach ($structure as $node) {
        if ($node['type'] === 'directory') {
            $activeClass = $node['active'] ? 'active-directory' : '';
            echo '<li class="folder-node ' . $activeClass . '" data-path="' . htmlspecialchars($node['path']) . '"><i class="fas fa-folder folder-icon"></i>';
            echo '<span class="caret">' . htmlspecialchars($node['name']) . '</span>';
            echo '<ul class="nested ' . $activeClass . '">';
            if (!empty($node['children'])) {
                renderDirectoryTree($node['children']);
            }
            echo '</ul>';
            echo '</li>';
        }
    }
    echo '</ul>';
}

$current_dir = $base_dir;
if (isset($_GET['dir']) && is_dir(urldecode($_GET['dir']))) {
    $current_dir = urldecode($_GET['dir']);
}

$directory_structure = $base_dir ? getDirectoryStructure($base_dir, $current_dir) : [];

$items = $current_dir ? scandir($current_dir) : [];
$files = [];
$folders = [];

foreach ($items as $item) {
    if (!in_array($item, [".", ".."])) {
        if (is_dir($current_dir . DIRECTORY_SEPARATOR . $item)) {
            $folders[] = $item;
        } else {
            $files[] = $item;
        }
    }
}

function getFileIcon($filename) {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    switch (strtolower($ext)) {
        case 'pdf':
            return ['fas fa-file-pdf', '#FF0000'];
        case 'doc':
        case 'docx':
            return ['fas fa-file-word', '#007BFF'];
        case 'xls':
        case 'xlsx':
            return ['fas fa-file-excel', '#28A745'];
        case 'ppt':
        case 'pptx':
            return ['fas fa-file-powerpoint', '#DC3545'];
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            return ['fas fa-file-image', '#FFC107'];
        case 'txt':
            return ['fas fa-file-alt', '#6C757D'];
        case 'zip':
        case 'rar':
            return ['fas fa-file-archive', '#FFD700'];
        case 'bat':
            return ['fas fa-file-code', '#000000'];
        default:
            return ['fas fa-file', '#6C757D'];
    }
}

function generateBatFile($path, $server_name) {
    $relative_path = str_replace('D:\\', "\\\\$server_name\\", $path);
    $relative_path = preg_replace('/\\\\+/', '\\', $relative_path);
    if (substr($relative_path, 0, 1) !== '\\') {
        $relative_path = '\\' . $relative_path;
    }
    if (substr($relative_path, 0, 2) !== '\\\\') {
        $relative_path = '\\' . $relative_path;
    }
    $batContent = 'start "" "' . $relative_path . '"';
    return $batContent;
}

function addToHistory($conn, $user_id, $accion, $nombre_archivo, $localizacion, $descripcion) {
    $sql = "INSERT INTO historial (usuario_id, accion, nombre_archivo, localizacion, descripcion, fecha) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $user_id, $accion, $nombre_archivo, $localizacion, $descripcion);
    $stmt->execute();
}

if (isset($_POST['download'])) {
    $path = urldecode($_POST['download']);
    if (file_exists($path)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($path).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit();
    }
}

if (isset($_POST['open'])) {
    $path = urldecode($_POST['open']);
    if (file_exists($path)) {
        $server_name = 'SERVIDOR'; 
        $batContent = generateBatFile($path, $server_name);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="open_file.bat"');
        header('Content-Length: ' . strlen($batContent));
        echo $batContent;
        exit();
    }
}

if (isset($_POST['create_folder']) && isset($_POST['current_dir'])) {
    $new_folder_name = $_POST['folder_name'];
    $current_dir = urldecode($_POST['current_dir']);
    $new_folder_path = $current_dir . DIRECTORY_SEPARATOR . $new_folder_name;

    if (!file_exists($new_folder_path)) {
        mkdir($new_folder_path, 0777, true);
        addToHistory($conn, $user_id, 'crear', $new_folder_name, $new_folder_path, 'Carpeta creada');
        header("Location: gestion.php?dir=" . urlencode($current_dir));
        exit();
    } else {
        echo "<script>alert('La carpeta ya existe.');</script>";
    }
}

if (isset($_POST['rename']) && isset($_POST['current_dir'])) {
    $current_name = $_POST['current_name'];
    $new_name = $_POST['new_name'];
    $extension = pathinfo($current_name, PATHINFO_EXTENSION);
    $current_dir = urldecode($_POST['current_dir']);
    $current_path = $current_dir . DIRECTORY_SEPARATOR . $current_name;
    $new_path = $current_dir . DIRECTORY_SEPARATOR . $new_name . ($extension ? '.' . $extension : '');

    if (file_exists($current_path) && !file_exists($new_path)) {
        rename($current_path, $new_path);
        addToHistory($conn, $user_id, 'renombrar', $current_name, $current_path, 'Renombrado a ' . $new_name);
        header("Location: gestion.php?dir=" . urlencode($current_dir));
        exit();
    } else {
        echo "<script>alert('Error al renombrar.');</script>";
    }
}

if (isset($_POST['delete'])) {
    $path = urldecode($_POST['delete']);
    if (file_exists($path)) {
        $is_dir = is_dir($path);
        if ($is_dir) {
            $it = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
            rmdir($path);
        } else {
            unlink($path);
        }
        addToHistory($conn, $user_id, 'eliminar', basename($path), $path, $is_dir ? 'Carpeta eliminada' : 'Archivo eliminado');
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Información - GESTOR DE ARCHIVOS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="shared_styles.css">
</head>
<body>
    <header>
        <h1>Gestión de Información</h1>
        <form method="post" action="gestion.php">
            <label for="dependencia_id">Dependencia:</label>
            <select id="dependencia_id" name="dependencia_id" onchange="this.form.submit()">
                <?php foreach ($dependencias as $dep): ?>
                    <option value="<?php echo $dep['id']; ?>" <?php if ($dep['id'] == $dependencia_id) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($dep['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <a href="logout.php">Cerrar sesión</a>
    </header>
    <div class="info-container">
        <span class="welcome-message">Bienvenido, <?php echo htmlspecialchars($user_name); ?></span>
        <div class="space-bar">
            <div class="space-bar-fill" style="width: <?php echo $used_percent; ?>%;"></div>
        </div>
        <span><?php echo $used_space_gb; ?> GB / <?php echo $total_space_gb; ?> GB</span>
        <div class="clock" id="clock"></div>
    </div>
    <div class="search-bar">
        <form method="get" action="gestion.php">
            <input type="text" name="search" placeholder="Buscar archivos o carpetas...">
            <button type="submit"><i class="fas fa-search"></i> Buscar</button>
        </form>
    </div>
    <main>
        <div class="left-panel">
            <h2>Directorios</h2>
            <?php if (!empty($directory_structure)): ?>
                <?php renderDirectoryTree($directory_structure); ?>
            <?php else: ?>
                <p>No se encontraron directorios.</p>
            <?php endif; ?>
        </div>
        <div class="content-panel">
            <h2>Archivos en el Directorio</h2>
            <div class="new-folder-container">
                <?php if ($current_dir && $current_dir != $base_dir): ?>
                    <a href="gestion.php?dir=<?php echo urlencode(dirname($current_dir)); ?>"><img src="icono/atras.png" alt="Atrás" style="width: 32px; height: 32px;"></a>
                <?php endif; ?>
                <form method="post" action="gestion.php" style="display: flex;">
                    <input type="text" name="folder_name" placeholder="Nombre de la nueva carpeta" required>
                    <input type="hidden" name="current_dir" value="<?php echo htmlspecialchars($current_dir); ?>">
                    <input type="hidden" name="create_folder" value="true">
                    <button type="submit">Crear Carpeta</button>
                </form>
                <button class="rename-button" id="rename-btn"><i class="fas fa-edit"></i> Renombrar</button>
            </div>
            <ul>
                <?php foreach ($folders as $folder): ?>
                    <li class="folder droppable" data-path="<?php echo htmlspecialchars($current_dir . DIRECTORY_SEPARATOR . $folder); ?>">
                        <i class="fas fa-folder folder-icon"></i>
                        <span class="item-name"><?php echo htmlspecialchars($folder); ?></span>
                    </li>
                <?php endforeach; ?>
                <?php foreach ($files as $file): ?>
                    <?php list($icon, $color) = getFileIcon($file); ?>
                    <li class="file draggable" draggable="true" data-path="<?php echo htmlspecialchars($current_dir . DIRECTORY_SEPARATOR . $file); ?>">
                        <i class="<?php echo $icon; ?> file-icon" style="color: <?php echo $color; ?>;"></i>
                        <span class="item-name"><?php echo htmlspecialchars($file); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="vertical-buttons">
                <button id="open"><i class="fas fa-folder-open"></i> Abrir</button>
                <button id="download"><i class="fas fa-download"></i> Descargar</button>
                <button id="delete"><i class="fas fa-trash-alt"></i> Eliminar</button>
                <button id="history"><i class="fas fa-history"></i> Historial</button>
                <a href="upload_file.php?dir=<?php echo urlencode($current_dir); ?>" class="upload-button"><i class="fas fa-upload"></i> Subir Archivo</a>
                <a href="upload_folder.php?dir=<?php echo urlencode($current_dir); ?>" class="upload-button"><i class="fas fa-folder-plus"></i> Subir Carpeta</a>
            </div>
        </div>
    </main>

    <div id="renameModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Renombrar Archivo o Carpeta</h2>
            <form id="renameForm" method="post" action="gestion.php">
                <div class="rename-input-container">
                    <input type="text" name="new_name" placeholder="Nuevo nombre" required style="flex: 1; padding: 10px; font-size: 16px;">
                    <span class="file-extension"></span>
                </div>
                <input type="hidden" name="current_name">
                <input type="hidden" name="current_dir" value="<?php echo htmlspecialchars($current_dir); ?>">
                <input type="hidden" name="rename" value="true">
                <button type="submit">Guardar</button>
            </form>
        </div>
    </div>

    <div id="historyModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Historial de Archivo o Carpeta</h2>
            <div id="historyContent"></div>
        </div>
    </div>

    <div id="codeModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Ingrese el Código de Seguridad</h2>
            <form id="codeForm" method="post" action="gestion.php">
                <input type="number" id="securityCode" name="security_code" required>
                <button type="submit">Confirmar</button>
            </form>
        </div>
    </div>

    <div id="successMessage" class="success-message">
        <?php
        if (isset($_SESSION['upload_success'])) {
            echo $_SESSION['upload_success'];
            unset($_SESSION['upload_success']);
        }
        ?>
    </div>

    <div id="errorMessage" class="error-message">
        <?php
        if (isset($_SESSION['upload_error'])) {
            echo $_SESSION['upload_error'];
            unset($_SESSION['upload_error']);
        }
        ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var toggles = document.querySelectorAll('.caret');
            toggles.forEach(function(toggle) {
                toggle.addEventListener('click', function() {
                    var nested = this.parentElement.querySelector(".nested");
                    nested.classList.toggle("active");
                    this.classList.toggle("caret-down");
                });
            });

            var folderNodes = document.querySelectorAll('.folder-node');
            folderNodes.forEach(function(node) {
                node.addEventListener('click', function(event) {
                    if (event.target.tagName !== 'SPAN') {
                        window.location.href = 'gestion.php?dir=' + encodeURIComponent(node.getAttribute('data-path'));
                    }
                });
            });

            var selectedFiles = [];
            var files = document.querySelectorAll('.file, .folder');

            files.forEach(function(file) {
                file.addEventListener('click', function() {
                    files.forEach(f => f.classList.remove('selected'));
                    this.classList.add('selected');
                    selectedFiles = [file.getAttribute('data-path')];
                });

                file.addEventListener('dblclick', function() {
                    if (this.classList.contains('folder')) {
                        window.location.href = 'gestion.php?dir=' + encodeURIComponent(file.getAttribute('data-path'));
                    }
                });
            });

            document.querySelectorAll('.folder-node[data-path="<?php echo htmlspecialchars($current_dir); ?>"]').forEach(function(node) {
                node.querySelector('.caret').classList.add('current-directory');
            });

            document.getElementById('download').addEventListener('click', function() {
                if (selectedFiles.length > 0) {
                    selectedFiles.forEach(function(file) {
                        var form = document.createElement('form');
                        form.method = 'post';
                        form.action = 'gestion.php';

                        var fileInput = document.createElement('input');
                        fileInput.type = 'hidden';
                        fileInput.name = 'download';
                        fileInput.value = file;

                        form.appendChild(fileInput);
                        document.body.appendChild(form);
                        form.submit();
                    });
                }
            });

            document.getElementById('open').addEventListener('click', function() {
                if (selectedFiles.length > 0) {
                    selectedFiles.forEach(function(file) {
                        var form = document.createElement('form');
                        form.method = 'post';
                        form.action='gestion.php';

                        var fileInput = document.createElement('input');
                        fileInput.type = 'hidden';
                        fileInput.name = 'open';
                        fileInput.value = file;

                        form.appendChild(fileInput);
                        document.body.appendChild(form);
                        form.submit();
                    });
                }
            });

            var renameModal = document.getElementById('renameModal');
            var renameForm = document.getElementById('renameForm');
            var closeBtns = document.querySelectorAll('.close');

            document.getElementById('rename-btn').addEventListener('click', function() {
                if (selectedFiles.length === 1) {
                    var selectedFile = selectedFiles[0];
                    var currentName = selectedFile.split('\\').pop().split('/').pop();
                    var extension = currentName.split('.').pop();
                    var baseName = currentName.replace('.' + extension, '');

                    renameForm.querySelector('input[name="current_name"]').value = currentName;
                    renameForm.querySelector('input[name="new_name"]').value = baseName;
                    renameForm.querySelector('.file-extension').textContent = '.' + extension;
                    renameModal.style.display = 'block';
                } else {
                    alert('Seleccione un solo archivo o carpeta para renombrar.');
                }
            });

            closeBtns.forEach(function(closeBtn) {
                closeBtn.onclick = function() {
                    closeBtn.parentElement.parentElement.style.display = 'none';
                }
            });

            window.onclick = function(event) {
                if (event.target.classList.contains('modal')) {
                    event.target.style.display = 'none';
                }
            }

            document.getElementById('delete').addEventListener('click', function() {
                if (selectedFiles.length === 1) {
                    var codeModal = document.getElementById('codeModal');
                    codeModal.style.display = 'block';
                } else {
                    alert('Seleccione un archivo o carpeta para eliminar.');
                }
            });

            document.getElementById('codeForm').addEventListener('submit', function(event) {
                event.preventDefault();
                var securityCode = document.getElementById('securityCode').value;
                var form = document.createElement('form');
                form.method = 'post';
                form.action = 'gestion.php';

                var fileInput = document.createElement('input');
                fileInput.type = 'hidden';
                fileInput.name = 'delete';
                fileInput.value = selectedFiles[0];

                var codeInput = document.createElement('input');
                codeInput.type = 'hidden';
                codeInput.name = 'security_code';
                codeInput.value = securityCode;

                form.appendChild(fileInput);
                form.appendChild(codeInput);
                document.body.appendChild(form);
                form.submit();
            });

            document.getElementById('history').addEventListener('click', function() {
                if (selectedFiles.length === 1) {
                    var historyModal = document.getElementById('historyModal');
                    var historyContent = document.getElementById('historyContent');
                    var path = selectedFiles[0];
                    var xhr = new XMLHttpRequest();
                    xhr.open('GET', 'get_history.php?path=' + encodeURIComponent(path), true);
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            historyContent.innerHTML = xhr.responseText;
                            historyModal.style.display = 'block';
                        }
                    };
                    xhr.send();
                } else {
                    alert('Seleccione un archivo o carpeta para ver su historial.');
                }
            });

            function updateClock() {
                var now = new Date();
                var hours = now.getHours() % 12 || 12;
                var minutes = now.getMinutes();
                var seconds = now.getSeconds();
                var ampm = now.getHours() >= 12 ? 'PM' : 'AM';

                var timeString = hours.toString().padStart(2, '0') + ':' +
                                minutes.toString().padStart(2, '0') + ':' +
                                seconds.toString().padStart(2, '0') + ' ' + ampm;

                document.getElementById('clock').textContent = timeString;
            }

            setInterval(updateClock, 1000);
            updateClock();
        });
    </script>
</body>
</html>








