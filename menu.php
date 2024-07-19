<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Principal - GESTOR DE ARCHIVOS</title>
</head>
<body>
    <h1>Bienvenido al Menú Principal</h1>
    <p>Usuario ID: <?php echo $_SESSION['user_id']; ?></p>
    <a href="logout.php">Cerrar sesión</a>
</body>
</html>
