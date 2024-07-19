<?php
// archivo: gestion/admin/seguridad.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include '../conexion.php';

// Obtener el mensaje de éxito si existe
$mensaje = isset($_GET['mensaje']) ? $_GET['mensaje'] : '';

// Insertar o actualizar el código
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo = intval($_POST['codigo']);

    // Validar que el código sea de 4 dígitos
    if ($codigo >= 1000 && $codigo <= 9999) {
        // Obtener el código actual
        $sql = "SELECT * FROM codigo_seguridad ORDER BY id DESC LIMIT 1";
        $result = $conn->query($sql);
        $codigo_actual = $result->fetch_assoc();

        if ($codigo_actual) {
            // Actualizar el código existente
            $sql = "UPDATE codigo_seguridad SET codigo = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $codigo, $codigo_actual['id']);
        } else {
            // Insertar el nuevo código
            $sql = "INSERT INTO codigo_seguridad (codigo) VALUES (?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $codigo);
        }
        $stmt->execute();
        
        $mensaje = "Código guardado con éxito.";
    } else {
        $mensaje = "El código debe ser un número de 4 dígitos.";
    }
}

// Obtener el código actual
$sql = "SELECT * FROM codigo_seguridad ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);
$codigo_actual = $result->fetch_assoc();

$codigo = isset($codigo_actual['codigo']) ? $codigo_actual['codigo'] : null;
$fecha_creacion = isset($codigo_actual['fecha_creacion']) ? $codigo_actual['fecha_creacion'] : null;

// Calcular el código de seguridad
$dia = date('d');
$año = date('Y');
$codigo_seguridad = $codigo ? $dia * $año * $codigo : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de Seguridad - Gestión</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        .codigo-seguridad {
            font-size: 3em;
            color: #0d2c4d;
            text-align: center;
            margin-top: 20px;
        }
        .mensaje {
            margin-top: 20px;
            color: green;
            text-align: center;
        }
        .error {
            margin-top: 20px;
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include '../inicio/menu.php'; ?>
    <main>
        <h2>Generar Código de Seguridad</h2>
        <form action="seguridad.php" method="post">
            <div class="form-group">
                <label for="codigo">Código (4 dígitos):</label>
                <input type="number" id="codigo" name="codigo" value="<?php echo $codigo; ?>" required>
            </div>
            <button type="submit"><?php echo $codigo ? 'Actualizar Código' : 'Guardar Código'; ?></button>
        </form>
        <?php if ($codigo_seguridad): ?>
            <div class="codigo-seguridad">
                Código de Seguridad: <?php echo $codigo_seguridad; ?>
            </div>
        <?php endif; ?>
        <?php if ($mensaje): ?>
            <div class="mensaje">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>

