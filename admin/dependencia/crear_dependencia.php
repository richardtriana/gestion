<?php
// archivo: gestion/admin/dependencia/crear_dependencia.php

include '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $tamaño = $_POST['tamaño'];

    // Obtener la ubicación de la carpeta raíz desde la tabla 'raiz'
    $sql = "SELECT ruta_completa FROM raiz LIMIT 1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $ruta_raiz = $row['ruta_completa'];
    } else {
        $mensaje = "Error: No se encontró la carpeta raíz.";
        header("Location: dependencia.php?mensaje=" . urlencode($mensaje));
        exit;
    }

    $ruta = $ruta_raiz . DIRECTORY_SEPARATOR . $nombre;

    // Crear el directorio en la carpeta raíz
    if (!is_dir($ruta)) {
        if (mkdir($ruta, 0777, true)) {
            // Insertar los datos en la tabla 'dependencia'
            $sql = "INSERT INTO dependencia (nombre, tamaño, ruta) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sis", $nombre, $tamaño, $ruta);
            if ($stmt->execute()) {
                $mensaje = "Dependencia '$nombre' creada con éxito y guardada en la base de datos.";
            } else {
                $mensaje = "Error al guardar la dependencia en la base de datos.";
            }
        } else {
            $mensaje = "Error al crear el directorio '$nombre'.";
        }
    } else {
        $mensaje = "El directorio '$nombre' ya existe.";
    }

    // Redirigir de vuelta a la página de gestión de dependencias con el mensaje de éxito
    header("Location: dependencia.php?mensaje=" . urlencode($mensaje));
    exit;
}
?>


