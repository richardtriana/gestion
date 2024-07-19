<?php
// archivo: raiz/raiz.php

// Incluir el menú
include '../inicio/menu.php';
include '../conexion.php';

// Obtener el mensaje de éxito si existe
$mensaje = isset($_GET['mensaje']) ? $_GET['mensaje'] : '';

// Obtener las rutas guardadas en la base de datos
$sql = "SELECT * FROM raiz";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Directorio Raíz</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <script src="../js/funciones.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var mensaje = "<?php echo $mensaje; ?>";
            if (mensaje) {
                mostrarMensajeExito(mensaje);
            }
        });
    </script>
</head>
<body>
    <main>
        <h2>Crear Directorio</h2>
        <form id="formCrearCarpeta" action="crear_carpeta.php" method="post" onsubmit="return validarFormulario()">
            <div class="form-group">
                <label for="ubicacion">Ubicación:</label>
                <input type="text" id="ubicacion" name="ubicacion" placeholder="Escribe o pega la ruta de la ubicación" required>
            </div>
            <div class="form-group">
                <label for="nombreCarpeta">Nombre de la Carpeta:</label>
                <input type="text" id="nombreCarpeta" name="nombreCarpeta" required>
            </div>
            <button type="submit">Crear Carpeta</button>
        </form>
        
        <h2>Rutas Guardadas</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ubicación</th>
                    <th>Nombre de la Carpeta</th>
                    <th>Ruta Completa</th>
                    <th>Fecha de Creación</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['ubicacion']; ?></td>
                            <td><?php echo $row['nombre_carpeta']; ?></td>
                            <td><?php echo $row['ruta_completa']; ?></td>
                            <td><?php echo $row['fecha_creacion']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No hay rutas guardadas</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
    <!-- Modal de éxito -->
    <div id="modalExito" class="modal">
        <div class="modal-contenido">
            <h2 id="mensajeExito" style="color: green;"></h2>
        </div>
    </div>
</body>
</html>






