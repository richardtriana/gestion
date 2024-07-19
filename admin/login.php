<?php
session_start();
include 'conexion.php'; // Asegúrate de que esta ruta sea correcta

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);

    // Verificar que el usuario y la contraseña sean correctos
    $sql = "SELECT id, contraseña FROM administradores WHERE username='$username' AND estado=1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Verificar la contraseña encriptada
        if (password_verify($password, $row['contraseña'])) {
            $_SESSION['admin_id'] = $row['id'];
            // Redirigir a dashboard.php después de iniciar sesión
            header("Location: dashboard/dashboard.php");
            exit();
        } else {
            $error = "Usuario o contraseña incorrectos.";
        }
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administracion</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #2c3e50, #4ca1af);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: white;
        }
        .modal {
            display: block;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }
        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            width: 400px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            animation: modalopen 0.5s;
            text-align: center;
            color: #333;
        }
        @keyframes modalopen {
            from {opacity: 0;}
            to {opacity: 1;}
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            color: #4ca1af;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        form label {
            width: 100%;
            text-align: left;
            margin-top: 10px;
        }
        form input[type="text"],
        form input[type="password"] {
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
            background: #fff;
            color: #333;
        }
        form input[type="text"]:focus,
        form input[type="password"]:focus {
            outline: none;
            border: 1px solid #4ca1af;
            box-shadow: 0 0 10px #4ca1af;
        }
        form input[type="submit"] {
            background-color: #4ca1af;
            color: white;
            border: none;
            padding: 10px;
            margin-top: 20px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
            width: 100%;
            text-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
        form input[type="submit"]:hover {
            background-color: #2c3e50;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <div class="title">Administradores</div>
            <form method="post" action="login.php">
                <label for="username">Usuario:</label>
                <input type="text" id="username" name="username" required>
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
                <input type="submit" value="Ingresar">
            </form>
            <?php if($error) { echo "<p class='error'>$error</p>"; } ?>
        </div>
    </div>
</body>
</html>

