<?php
// archivo: gestion/usuarios/editar_usuario.php

include '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $cedula = $_POST['cedula'];
    $celular = $_POST['celular'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $estado = $_POST['estado'];
    $dependencias = isset($_POST['editar_dependencia']) ? $_POST['editar_dependencia'] : [];

    // Actualizar los datos en la tabla 'usuarios'
    if (!empty($password)) {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET nombres=?, apellidos=?, cedula=?, celular=?, fecha_nacimiento=?, username=?, contraseña=?, estado=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssi", $nombres, $apellidos, $cedula, $celular, $fecha_nacimiento, $username, $password, $estado, $id);
    } else {
        $sql = "UPDATE usuarios SET nombres=?, apellidos=?, cedula=?, celular=?, fecha_nacimiento=?, username=?, estado=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssi", $nombres, $apellidos, $cedula, $celular, $fecha_nacimiento, $username, $estado, $id);
    }

    if ($stmt->execute()) {
        // Eliminar las dependencias existentes
        $sql_del = "DELETE FROM usuarios_dependencias WHERE usuario_id=?";
        $stmt_del = $conn->prepare($sql_del);
        $stmt_del->bind_param("i", $id);
        $stmt_del->execute();

        // Insertar las dependencias nuevas
        foreach ($dependencias as $dependencia_id) {
            $sql_dep = "INSERT INTO usuarios_dependencias (usuario_id, dependencia_id) VALUES (?, ?)";
            $stmt_dep = $conn->prepare($sql_dep);
            $stmt_dep->bind_param("ii", $id, $dependencia_id);
            $stmt_dep->execute();
        }

        $mensaje = "Usuario actualizado con éxito.";
    } else {
        $mensaje = "Error al actualizar el usuario.";
    }

    // Redirigir de vuelta a la página de gestión de usuarios con el mensaje de éxito
    header("Location: usuarios.php?mensaje=" . urlencode($mensaje));
    exit;
}
?>

