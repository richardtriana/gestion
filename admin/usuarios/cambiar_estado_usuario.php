<?php
// archivo: gestion/usuarios/cambiar_estado_usuario.php

include '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];

    // Obtener el estado actual del usuario
    $sql = "SELECT estado FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    $nuevo_estado = $usuario['estado'] ? 0 : 1;

    // Cambiar el estado del usuario
    $sql = "UPDATE usuarios SET estado = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $nuevo_estado, $id);
    $stmt->execute();

    echo "Estado cambiado con éxito";
}
?>
