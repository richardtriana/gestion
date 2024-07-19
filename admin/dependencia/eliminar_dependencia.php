<?php
// archivo: gestion/admin/dependencia/eliminar_dependencia.php

include '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];

    // Obtener la dependencia actual
    $sql = "SELECT * FROM dependencia WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $dependencia = $result->fetch_assoc();

    // Eliminar el directorio si existe
    $ruta = $dependencia['ruta'];
    if (is_dir($ruta)) {
        rmdir($ruta);
    }

    // Eliminar la dependencia de la base de datos
    $sql = "DELETE FROM dependencia WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $mensaje = "Dependencia eliminada con éxito.";
    } else {
        $mensaje = "Error al eliminar la dependencia.";
    }

    // Redirigir de vuelta a la página de gestión de dependencias con el mensaje de éxito
    header("Location: dependencia.php?mensaje=" . urlencode($mensaje));
    exit;
}
?>

