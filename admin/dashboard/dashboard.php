<?php
include '../conexion.php';
include '../inicio/menu.php';

// Query para obtener datos de las tablas
$sql_administradores = "SELECT COUNT(*) AS total FROM administradores";
$result_administradores = $conn->query($sql_administradores);
$row_administradores = $result_administradores->fetch_assoc();
$total_administradores = $row_administradores['total'];

$sql_codigos = "SELECT COUNT(*) AS total FROM codigo_seguridad";
$result_codigos = $conn->query($sql_codigos);
$row_codigos = $result_codigos->fetch_assoc();
$total_codigos = $row_codigos['total'];

$sql_ultimo_respaldo = "SELECT fecha_fin FROM respaldo WHERE estado = 'correcto' ORDER BY fecha_fin DESC LIMIT 1";
$result_ultimo_respaldo = $conn->query($sql_ultimo_respaldo);
$row_ultimo_respaldo = $result_ultimo_respaldo->fetch_assoc();
$ultimo_respaldo = $row_ultimo_respaldo ? $row_ultimo_respaldo['fecha_fin'] : 'No disponible';

$sql_usuarios = "SELECT COUNT(*) AS total FROM usuarios";
$result_usuarios = $conn->query($sql_usuarios);
$row_usuarios = $result_usuarios->fetch_assoc();
$total_usuarios = $row_usuarios['total'];

$sql_dependencias = "SELECT COUNT(*) AS total FROM dependencia";
$result_dependencias = $conn->query($sql_dependencias);
$row_dependencias = $result_dependencias->fetch_assoc();
$total_dependencias = $row_dependencias['total'];

$sql_detalle_dependencias = "SELECT nombre, tamaño, ruta FROM dependencia";
$result_detalle_dependencias = $conn->query($sql_detalle_dependencias);
$dependencias = [];
while ($row = $result_detalle_dependencias->fetch_assoc()) {
    // Calcular el espacio ocupado leyendo el tamaño de la carpeta
    $ruta = $row['ruta'];
    $espacio_ocupado = folderSize($ruta) / 1024 / 1024 / 1024; // Convertir a GB
    $row['espacio_ocupado'] = $espacio_ocupado;
    $dependencias[] = $row;
}

function folderSize($dir) {
    $size = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file) {
        $size += $file->getSize();
    }
    return $size;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .dashboard-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            padding: 20px;
        }
        .widget {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 250px;
            margin: 10px;
            padding: 20px;
            text-align: center;
            transition: transform 0.3s;
        }
        .widget:hover {
            transform: scale(1.05);
        }
        .widget i {
            font-size: 50px;
            margin-bottom: 10px;
        }
        .widget h3 {
            margin: 0;
            font-size: 24px;
        }
        .widget p {
            margin: 10px 0 0;
            font-size: 18px;
        }
        .blue {
            color: #0d2c4d;
        }
        .green {
            color: #28a745;
        }
        .purple {
            color: #6f42c1;
        }
        .red {
            color: #dc3545;
        }
        .orange {
            color: #fd7e14;
        }
        .progress-bar {
            background-color: #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            height: 20px;
            margin-top: 10px;
        }
        .progress {
            height: 100%;
            border-radius: 8px;
        }
        .section-title {
            width: 100%;
            text-align: center;
            padding: 10px 20px;
            font-size: 20px;
            color: #0d2c4d;
            margin-top: 30px;
        }
        .microsoft-folder-color {
            color: #EFA94A; /* Color similar al de las carpetas de Microsoft */
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="widget blue">
            <i class="fas fa-user-shield"></i>
            <h3>Administradores</h3>
            <p><?php echo $total_administradores; ?></p>
        </div>
        <div class="widget green">
            <i class="fas fa-key"></i>
            <h3>Códigos de Seguridad</h3>
            <p><?php echo $total_codigos; ?></p>
        </div>
        <div class="widget purple">
            <i class="fas fa-clock"></i>
            <h3>Último Respaldo</h3>
            <p><?php echo $ultimo_respaldo; ?></p>
        </div>
        <div class="widget red">
            <i class="fas fa-users"></i>
            <h3>Usuarios</h3>
            <p><?php echo $total_usuarios; ?></p>
        </div>
        <div class="widget orange">
            <i class="fas fa-database"></i>
            <h3>Dependencias</h3>
            <p><?php echo $total_dependencias; ?></p>
        </div>
    </div>
    
    <div class="section-title">Almacenamiento de Dependencias</div>
    <div class="dashboard-container">
        <?php foreach ($dependencias as $dep): 
            $porcentaje = ($dep['espacio_ocupado'] / $dep['tamaño']) * 100;
        ?>
        <div class="widget">
            <i class="fas fa-folder microsoft-folder-color"></i>
            <h3><?php echo $dep['nombre']; ?></h3>
            <p><?php echo round($dep['espacio_ocupado'], 2) . ' GB / ' . $dep['tamaño'] . ' GB'; ?></p>
            <div class="progress-bar">
                <div class="progress" style="width: <?php echo $porcentaje; ?>%; background-color: <?php echo ($porcentaje < 50) ? '#28a745' : (($porcentaje < 80) ? '#fd7e14' : '#dc3545'); ?>;"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>

