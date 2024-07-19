<?php
// archivo: gestion/admin/dependencia/editar_dependencia.php

include '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $tamaño = $_POST['tamaño'];

    // Obtener la dependencia actual
    $sql = "SELECT * FROM dependencia WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $dependencia = $result->fetch_assoc();

    // Obtener la ubicación de la carpeta raíz desde la tabla 'raiz'
    $sql = "SELECT ruta_completa FROM raiz LIMIT 1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $ruta_raiz = $row['ruta_completa'];
    } else {
        $mensaje = "Error: No se encontró la carpeta raíz.";
        header("Location: dependencia.php?mensaje=" . urlencode($mensaje));
        exit;
    }

    // Renombrar el directorio si el nombre ha cambiado
    $ruta_antigua = $dependencia['ruta'];
    $ruta_nueva = $ruta_raiz . DIRECTORY_SEPARATOR . $nombre;
    if ($ruta_antigua !== $ruta_nueva && is_dir($ruta_antigua)) {
        rename($ruta_antigua, $ruta_nueva);
    }

    // Actualizar los datos en la base de datos
    $sql = "UPDATE dependencia SET nombre = ?, tamaño = ?, ruta = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisi", $nombre, $tamaño, $ruta_nueva, $id);
    if ($stmt->execute()) {
        $mensaje = "Dependencia '$nombre' actualizada con éxito.";
    } else {
        $mensaje = "Error al actualizar la dependencia.";
    }

    // Redirigir de vuelta a la página de gestión de dependencias con el mensaje de éxito
    header("Location: dependencia.php?mensaje=" . urlencode($mensaje));
    exit;
}
?>



