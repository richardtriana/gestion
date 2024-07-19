<?php
// archivo: gestion/admin/dependencia/obtener_dependencia.php

include '../conexion.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "SELECT * FROM dependencia WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $dependencia = $result->fetch_assoc();

    echo json_encode($dependencia);
}
?>
