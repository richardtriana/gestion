<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'admin/conexion.php';

$file = urldecode($_GET['file']);
$sql = "SELECT h.accion, h.nombre_archivo, h.localizacion, h.descripcion, h.fecha, u.nombres AS usuario 
        FROM historial h
        JOIN usuarios u ON h.usuario_id = u.id
        WHERE h.localizacion = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $file);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<table class='history-table'>";
    echo "<tr><th>Acción</th><th>Archivo</th><th>Ubicación</th><th>Descripción</th><th>Fecha</th><th>Usuario</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['accion']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nombre_archivo']) . "</td>";
        echo "<td>" . htmlspecialchars($row['localizacion']) . "</td>";
        echo "<td>" . htmlspecialchars($row['descripcion']) . "</td>";
        echo "<td>" . htmlspecialchars($row['fecha']) . "</td>";
        echo "<td>" . htmlspecialchars($row['usuario']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No hay historial para este archivo o carpeta.</p>";
}
?>

