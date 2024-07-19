<?php
// archivo: gestion/usuarios/crear_usuario.php

include '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $cedula = $_POST['cedula'];
    $celular = $_POST['celular'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $estado = $_POST['estado'];
    $dependencias = $_POST['dependencia'];

    // Validar fecha de nacimiento
    if (empty($fecha_nacimiento) || $fecha_nacimiento == '0000-00-00') {
        $mensaje = "Fecha de nacimiento no válida.";
        header("Location: usuarios.php?mensaje=" . urlencode($mensaje));
        exit;
    }

    // Insertar los datos en la tabla 'usuarios'
    $sql = "INSERT INTO usuarios (nombres, apellidos, cedula, celular, fecha_nacimiento, username, contraseña, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $nombres, $apellidos, $cedula, $celular, $fecha_nacimiento, $username, $password, $estado);

    if ($stmt->execute()) {
        $usuario_id = $stmt->insert_id;

        // Insertar las dependencias del usuario
        foreach ($dependencias as $dependencia_id) {
            $sql_dep = "INSERT INTO usuarios_dependencias (usuario_id, dependencia_id) VALUES (?, ?)";
            $stmt_dep = $conn->prepare($sql_dep);
            $stmt_dep->bind_param("ii", $usuario_id, $dependencia_id);
            $stmt_dep->execute();
        }

        $mensaje = "Usuario creado con éxito.";
    } else {
        $mensaje = "Error al crear el usuario.";
    }

    // Redirigir de vuelta a la página de gestión de usuarios con el mensaje de éxito
    header("Location: usuarios.php?mensaje=" . urlencode($mensaje));
    exit;
}
?>


