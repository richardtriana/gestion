<?php
// archivo: gestion/admin/historial_respaldo.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include '../conexion.php';

// Obtener el historial de respaldos
$sql = "SELECT * FROM respaldo ORDER BY fecha_inicio DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Respaldo - Gestión</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <?php include '../inicio/menu.php'; ?>
    <main>
        <h2>Historial de Respaldo</h2>
        <table class="modern-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Estado</th>
                    <th>Fecha de Inicio</th>
                    <th>Fecha de Fin</th>
                    <th>Ruta</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['estado']; ?></td>
                    <td><?php echo $row['fecha_inicio']; ?></td>
                    <td><?php echo $row['fecha_fin']; ?></td>
                    <td><?php echo $row['ruta']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
