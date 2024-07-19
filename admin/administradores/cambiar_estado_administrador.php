<?php
// archivo: gestion/admin/administradores/cambiar_estado_administrador.php
include '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    // Aquí puedes añadir lógica para cambiar el estado del administrador (activar/inactivar)
    // Por ejemplo, podrías tener un campo "estado" en la base de datos y alternar su valor entre 0 y 1
    $sql = "UPDATE administradores SET estado = IF(estado = 1, 0, 1) WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
}
?>
