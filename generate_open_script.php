<?php
if (isset($_GET['path'])) {
    $path = urldecode($_GET['path']);
    $hostname = gethostname();
    $script = "";

    // Ajustar la ruta para incluir el nombre del equipo en la red local
    // Remover "D:" de la ruta para que sea compatible con la red local
    $network_path = str_replace('D:', '', $path);
    $network_path = str_replace('\\', '/', $network_path); // Convertir a barras normales
    $network_path = '\\\\' . $hostname . '\\' . trim($network_path, '/');

    // Detectar el sistema operativo
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Script para Windows
        $script = "start \"\" \"" . $network_path . "\"";
        $filename = "open_folder.bat";
    } else {
        // Script para macOS/Linux
        $script = "open " . escapeshellarg($network_path);
        $filename = "open_folder.sh";
    }

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($script));
    echo $script;
    exit;
} else {
    echo "No se ha proporcionado ninguna ruta.";
}
?>


