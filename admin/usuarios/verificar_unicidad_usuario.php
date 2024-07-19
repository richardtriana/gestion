<?php
// archivo: gestion/usuarios/verificar_unicidad_usuario.php

include '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cedula = $_POST['cedula'];
    $celular = $_POST['celular'];
    $id = isset($_POST['id']) ? $_POST['id'] : 0;

    $sql = "SELECT COUNT(*) AS count FROM usuarios WHERE (cedula = ? OR celular = ?) AND id != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $cedula, $celular, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $respuesta = [
        'cedulaExistente' => $row['count'] > 0,
        'celularExistente' => $row['count'] > 0
    ];

    echo json_encode($respuesta);
}
?>
