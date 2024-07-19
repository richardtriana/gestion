<?php
// archivo: gestion/admin/administradores/crear_administrador.php

include '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $cedula = $_POST['cedula'];
    $celular = $_POST['celular'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Comprobar si la cédula o el celular ya existen
    $sql = "SELECT * FROM administradores WHERE cedula = ? OR celular = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $cedula, $celular);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $mensaje = "La cédula o el celular ya están registrados";
    } else {
        $sql = "INSERT INTO administradores (nombre, apellido, cedula, celular, username, contraseña) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $nombre, $apellido, $cedula, $celular, $username, $password);

        if ($stmt->execute()) {
            $mensaje = "Administrador creado con éxito";
        } else {
            $mensaje = "Error al crear el administrador";
        }
    }

    // Redirigir a la página de administradores con el mensaje de éxito
    header("Location: administradores.php?mensaje=" . urlencode($mensaje));
    exit;
}
?>



