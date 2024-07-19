<?php
// archivo: gestion/admin/dependencia/dependencia.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include '../conexion.php';

// Obtener las dependencias
$sql = "SELECT * FROM dependencia LIMIT 15";
$result = $conn->query($sql);

// Obtener el mensaje de éxito si existe
$mensaje = isset($_GET['mensaje']) ? $_GET['mensaje'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dependencias - Gestión</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <script src="../js/funciones.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var mensaje = "<?php echo $mensaje; ?>";
            if (mensaje) {
                mostrarMensajeExito(mensaje);
                setTimeout(function() {
                    window.location.href = 'dependencia.php';
                }, 3000); // Espera 3 segundos y luego recarga la página sin el mensaje
            }
        });
    </script>
</head>
<body>
    <?php include '../inicio/menu.php'; ?>
    <main>
        <h2>Gestión de Dependencias</h2>
        <button class="crear" onclick="mostrarFormularioCrear()">Crear Dependencia</button>
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Tamaño (GB)</th>
                    <th>Ruta</th>
                    <th>Fecha de Creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['nombre']; ?></td>
                    <td><?php echo $row['tamaño']; ?></td>
                    <td><?php echo $row['ruta']; ?></td>
                    <td><?php echo $row['fecha_creacion']; ?></td>
                    <td>
                        <button class="editar" onclick="mostrarFormularioEditar(<?php echo $row['id']; ?>)">Editar</button>
                        <button class="eliminar" onclick="eliminarDependencia(<?php echo $row['id']; ?>)">Eliminar</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>
    <!-- Formulario modal para crear dependencia -->
    <div id="modalCrear" class="modal">
        <div class="modal-contenido">
            <span class="cerrar" onclick="cerrarFormularioCrear()">&times;</span>
            <h2>Crear Dependencia</h2>
            <form id="formCrearDependencia" action="crear_dependencia.php" method="post">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="tamaño">Tamaño (GB):</label>
                    <input type="number" id="tamaño" name="tamaño" required>
                </div>
                <button type="submit">Crear Dependencia</button>
            </form>
        </div>
    </div>

    <!-- Formulario modal para editar dependencia -->
    <div id="modalEditar" class="modal">
        <div class="modal-contenido">
            <span class="cerrar" onclick="cerrarFormularioEditar()">&times;</span>
            <h2>Editar Dependencia</h2>
            <form id="formEditarDependencia" action="editar_dependencia.php" method="post">
                <input type="hidden" id="editar_id" name="id">
                <div class="form-group">
                    <label for="editar_nombre">Nombre:</label>
                    <input type="text" id="editar_nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="editar_tamaño">Tamaño (GB):</label>
                    <input type="number" id="editar_tamaño" name="tamaño" required>
                </div>
                <button type="submit">Actualizar Dependencia</button>
            </form>
        </div>
    </div>

    <!-- Modal de éxito -->
    <div id="modalExito" class="modal">
        <div class="modal-contenido">
            <h2 id="mensajeExito" style="color: green;"></h2>
        </div>
    </div>
</body>
</html>


