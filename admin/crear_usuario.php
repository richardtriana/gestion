<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario - Gestión</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <div class="form-container">
        <h2>Crear Administrador</h2>
        <form action="crear_usuario.php" method="post">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>
            <label for="apellido">Apellido:</label>
            <input type="text" id="apellido" name="apellido" required>
            <label for="cedula">Cédula:</label>
            <input type="text" id="cedula" name="cedula" required>
            <label for="celular">Celular:</label>
            <input type="text" id="celular" name="celular" required>
            <label for="username">Usuario:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Crear Usuario</button>
        </form>
    </div>
    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        include 'conexion.php';
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $cedula = $_POST['cedula'];
        $celular = $_POST['celular'];
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO administradores (nombre, apellido, cedula, celular, username, contraseña) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $nombre, $apellido, $cedula, $celular, $username, $password);

        if ($stmt->execute()) {
            echo "<p>Usuario creado con éxito</p>";
        } else {
            echo "<p>Error al crear el usuario</p>";
        }
    }
    ?>
</body>
</html>
