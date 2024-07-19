<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'admin/conexion.php';

$path = urldecode($_GET['path']);

$sql = "SELECT h.*, u.nombres AS usuario FROM historial h
        JOIN usuarios u ON h.usuario_id = u.id
        WHERE h.localizacion = ?
        ORDER BY h.fecha DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $path);
$stmt->execute();
$result = $stmt->get_result();

echo "<table>";
echo "<thead><tr><th>Acción</th><th>Usuario</th><th>Nombre de Archivo</th><th>Localización</th><th>Descripción</th><th>Fecha</th></tr></thead>";
echo "<tbody>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['accion']) . "</td>";
    echo "<td>" . htmlspecialchars($row['usuario']) . "</td>";
    echo "<td>" . htmlspecialchars($row['nombre_archivo']) . "</td>";
    echo "<td>" . htmlspecialchars($row['localizacion']) . "</td>";
    echo "<td>" . htmlspecialchars($row['descripcion']) . "</td>";
    echo "<td>" . htmlspecialchars($row['fecha']) . "</td>";
    echo "</tr>";
}
echo "</tbody></table>";
?>
