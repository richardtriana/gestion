<?php
// archivo: gestion/admin/administradores/verificar_unicidad.php
include '../conexion.php';

$response = array('cedulaExistente' => false, 'celularExistente' => false);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cedula = $_POST['cedula'];
    $celular = $_POST['celular'];
    $id = isset($_POST['id']) ? $_POST['id'] : 0;

    $sql = "SELECT * FROM administradores WHERE (cedula = ? OR celular = ?) AND id != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $cedula, $celular, $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($row['cedula'] === $cedula) {
                $response['cedulaExistente'] = true;
            }
            if ($row['celular'] === $celular) {
                $response['celularExistente'] = true;
            }
        }
    }
}

echo json_encode($response);
?>
>
