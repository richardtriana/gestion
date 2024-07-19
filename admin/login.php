<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

include '../conexion.php'; // Ajuste de la ruta

if (!isset($conn)) {
    die("Error de conexión a la base de datos.");
}

$filtro_archivo = isset($_GET['archivo']) ? $_GET['archivo'] : '';
$filtro_accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$filtro_usuario = isset($_GET['usuario']) ? $_GET['usuario'] : '';
$filtro_fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
$filtro_fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';

$sql = "SELECT h.*, u.nombres, u.apellidos 
        FROM historial h
        JOIN usuarios u ON h.usuario_id = u.id
        WHERE (h.nombre_archivo LIKE ? OR ? = '')
        AND (h.accion LIKE ? OR ? = '')
        AND (u.nombres LIKE ? OR u.apellidos LIKE ? OR ? = '')
        AND (h.fecha >= ? OR ? = '')
        AND (h.fecha <= ? OR ? = '')
        ORDER BY h.fecha DESC";

$stmt = $conn->prepare($sql);

$filtro_archivo_param = "%$filtro_archivo%";
$filtro_accion_param = "%$filtro_accion%";
$filtro_usuario_param = "%$filtro_usuario%";
$filtro_fecha_desde_param = $filtro_fecha_desde ? $filtro_fecha_desde : '1970-01-01';
$filtro_fecha_hasta_param = $filtro_fecha_hasta ? $filtro_fecha_hasta : '9999-12-31';

$stmt->bind_param(
    'ssssssssss',
    $filtro_archivo_param, $filtro_archivo,
    $filtro_accion_param, $filtro_accion,
    $filtro_usuario_param, $filtro_usuario,
    $filtro_usuario_param, $filtro_usuario,
    $filtro_fecha_desde_param, $filtro_fecha_desde,
    $filtro_fecha_hasta_param, $filtro_fecha_hasta
);

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Archivos - Gestión</title>
    <link rel="stylesheet" href="../../shared_styles.css">
    <style>
        .filter-container {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .filter-container label,
        .filter-container input,
        .filter-container select,
        .filter-container button {
            margin-right: 10px;
            margin-bottom: 10px;
        }

        .filter-container input,
        .filter-container select {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .filter-container button {
            padding: 5px 10px;
            border: none;
            cursor: pointer;
        }

        .filter-container button.buscar {
            background-color: #4ca1af;
            color: white;
        }

        .filter-container button.limpiar {
            background-color: #dc3545;
            color: white;
        }

        .modal-contenido table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .modal-contenido th,
        .modal-contenido td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .modal-contenido th {
            background-color: #0d2c4d;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Historial de Archivos</h2>
        <div class="filter-container">
            <form method="get" action="historial.php">
                <label for="archivo">Archivo:</label>
                <input type="text" id="archivo" name="archivo" value="<?php echo htmlspecialchars($filtro_archivo); ?>">
                
                <label for="accion">Acción:</label>
                <input type="text" id="accion" name="accion" value="<?php echo htmlspecialchars($filtro_accion); ?>">
                
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" value="<?php echo htmlspecialchars($filtro_usuario); ?>">
                
                <label for="fecha_desde">Fecha desde:</label>
                <input type="date" id="fecha_desde" name="fecha_desde" value="<?php echo htmlspecialchars($filtro_fecha_desde); ?>">
                
                <label for="fecha_hasta">Fecha hasta:</label>
                <input type="date" id="fecha_hasta" name="fecha_hasta" value="<?php echo htmlspecialchars($filtro_fecha_hasta); ?>">
                
                <button type="submit" class="buscar">Buscar</button>
                <button type="button" class="limpiar" onclick="window.location.href='historial.php'">Limpiar</button>
            </form>
        </div>
        
        <div id="historialModal" class="modal">
            <div class="modal-contenido">
                <span class="cerrar" onclick="document.getElementById('historialModal').style.display='none'">&times;</span>
                <h2>Historial de Archivo o Carpeta</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Acción</th>
                            <th>Usuario</th>
                            <th>Nombre de Archivo</th>
                            <th>Localización</th>
                            <th>Descripción</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['accion']; ?></td>
                            <td><?php echo $row['nombres'] . ' ' . $row['apellidos']; ?></td>
                            <td><?php echo $row['nombre_archivo']; ?></td>
                            <td><?php echo $row['localizacion']; ?></td>
                            <td><?php echo $row['descripcion']; ?></td>
                            <td><?php echo $row['fecha']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>


