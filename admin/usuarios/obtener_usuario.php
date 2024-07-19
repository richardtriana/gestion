<?php
// archivo: gestion/usuarios/obtener_usuario.php

include '../conexion.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    // Obtener las dependencias del usuario
    $sql_dep = "SELECT dependencia_id FROM usuarios_dependencias WHERE usuario_id = ?";
    $stmt_dep = $conn->prepare($sql_dep);
    $stmt_dep->bind_param("i", $id);
    $stmt_dep->execute();
    $result_dep = $stmt_dep->get_result();
    $dependencias = [];
    while ($row_dep = $result_dep->fetch_assoc()) {
        $dependencias[] = $row_dep['dependencia_id'];
    }

    $usuario['dependencias'] = $dependencias;

    echo json_encode($usuario);
}
?>

