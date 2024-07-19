<?php
session_start();

$current_dir = urldecode($_GET['dir']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $success = true;
    $folder_name = $_POST['folder_name'];
    $target_dir = $current_dir . DIRECTORY_SEPARATOR . $folder_name;

    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    foreach ($_FILES['folder']['name'] as $key => $name) {
        $relative_path = $_FILES['folder']['name'][$key];
        $target_path = $target_dir . DIRECTORY_SEPARATOR . $relative_path;
        $target_sub_dir = dirname($target_path);

        if (!file_exists($target_sub_dir)) {
            mkdir($target_sub_dir, 0777, true);
        }

        if (!move_uploaded_file($_FILES['folder']['tmp_name'][$key], $target_path)) {
            $success = false;
            break;
        }
    }

    if ($success) {
        $_SESSION['upload_success'] = '¡Carpeta subida con éxito!';
    } else {
        $_SESSION['upload_error'] = 'Error al subir la carpeta.';
    }

    header('Location: gestion.php?dir=' . urlencode($current_dir));
    exit();
}
?>















