<?php
// archivo: gestion/admin/administradores/editar_administrador.php

include '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $cedula = $_POST['cedula'];
    $celular = $_POST['celular'];
    $username = $_POST['username'];
    $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // Comprobar si la cédula o el celular ya existen
    $sql = "SELECT * FROM administradores WHERE (cedula = ? OR celular = ?) AND id != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $cedula, $celular, $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>
                window.onload = function() {
                    mostrarMensajeExito('La cédula o el celular ya están registrados');
                };
              </script>";
    } else {
        if ($password) {
            $sql = "UPDATE administradores SET nombre = ?, apellido = ?, cedula = ?, celular = ?, username = ?, contraseña = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $nombre, $apellido, $cedula, $celular, $username, $password, $id);
        } else {
            $sql = "UPDATE administradores SET nombre = ?, apellido = ?, cedula = ?, celular = ?, username = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $nombre, $apellido, $cedula, $celular, $username, $id);
        }

        if ($stmt->execute()) {
            echo "<script>
                    window.onload = function() {
                        mostrarMensajeExito('Administrador actualizado con éxito');
                    };
                  </script>";
        } else {
            echo "<script>
                    window.onload = function() {
                        mostrarMensajeExito('Error al actualizar el administrador');
                    };
                  </script>";
        }
    }
}
?>




