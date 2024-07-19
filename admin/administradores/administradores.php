<?php
// archivo: gestion/admin/administradores/administradores.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include '../conexion.php';

// Obtener los administradores
$sql = "SELECT * FROM administradores LIMIT 15";
$result = $conn->query($sql);

// Obtener el mensaje de éxito si existe
$mensaje = isset($_GET['mensaje']) ? $_GET['mensaje'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administradores - Gestión</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #0d2c4d;
            color: white;
            padding: 10px 0;
            text-align: center;
        }

        header nav ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
        }

        header nav ul li {
            margin: 0 15px;
        }

        header nav ul li a {
            color: white;
            text-decoration: none;
        }

        header nav ul li a.logout {
            color: #a94442; /* Rojo oscuro */
            font-weight: bold;
        }

        main {
            margin: 20px;
        }

        h2 {
            color: #0d2c4d;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        button {
            padding: 10px 15px;
            border: none;
            cursor: pointer;
        }

        button.crear {
            background-color: #28a745;
            color: white;
        }

        button.filtrar {
            background-color: #17a2b8;
            color: white;
        }

        button.editar {
            background-color: #007bff;
            color: white;
        }

        button.inactivar {
            background-color: #ff8000; /* Naranja */
            color: white;
        }

        button.activar {
            background-color: #28a745;
            color: white;
        }

        button[type="submit"] {
            background-color: #0d2c4d;
            color: white;
        }

        table.modern-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table.modern-table th,
        table.modern-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        table.modern-table th {
            background-color: #0d2c4d;
            color: white;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-contenido {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 300px; /* Ajusta el tamaño del modal */
            text-align: center; /* Centra el contenido del modal */
            border-radius: 8px; /* Añade bordes redondeados */
        }

        .modal-contenido h2 {
            margin: 0;
        }

        .cerrar {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .cerrar:hover,
        .cerrar:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
    <script>
        function mostrarFormularioCrear() {
            document.getElementById('modalCrear').style.display = 'block';
        }

        function cerrarFormularioCrear() {
            document.getElementById('modalCrear').style.display = 'none';
        }

        function mostrarFormularioEditar(id) {
            // Cargar los datos del administrador desde el servidor y rellenar el formulario
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "obtener_administrador.php?id=" + id, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var admin = JSON.parse(xhr.responseText);
                    document.getElementById('editar_id').value = admin.id;
                    document.getElementById('editar_nombre').value = admin.nombre;
                    document.getElementById('editar_apellido').value = admin.apellido;
                    document.getElementById('editar_cedula').value = admin.cedula;
                    document.getElementById('editar_celular').value = admin.celular;
                    document.getElementById('editar_username').value = admin.username;
                    document.getElementById('modalEditar').style.display = 'block';
                }
            };
            xhr.send();
        }

        function cerrarFormularioEditar() {
            document.getElementById('modalEditar').style.display = 'none';
        }

        function filtrarAdministradores() {
            var nombre = document.getElementById('filtroNombre').value;
            var cedula = document.getElementById('filtroCedula').value;
            var telefono = document.getElementById('filtroTelefono').value;
            var cantidad = document.getElementById('cantidadResultados').value;

            // Aquí deberías realizar la lógica de filtrado, posiblemente haciendo una nueva solicitud al servidor con los filtros
            console.log("Filtros aplicados: ", nombre, cedula, telefono, cantidad);
        }

        function cambiarEstadoAdministrador(id) {
            // Realizar la solicitud al servidor para cambiar el estado del administrador
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "cambiar_estado_administrador.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Recargar la página para reflejar el cambio de estado
                    window.location.reload();
                }
            };
            xhr.send("id=" + id);
        }

        function mostrarMensajeExito(mensaje) {
            var modal = document.getElementById('modalExito');
            document.getElementById('mensajeExito').innerText = mensaje;
            modal.style.display = 'block';
            setTimeout(function() {
                modal.style.display = 'none';
                window.location.reload();
            }, 3000);
        }

        function mostrarMensajeError(mensaje) {
            var modal = document.getElementById('modalError');
            document.getElementById('mensajeError').innerText = mensaje;
            modal.style.display = 'block';
            setTimeout(function() {
                modal.style.display = 'none';
            }, 3000);
        }

        function cerrarModalError() {
            document.getElementById('modalError').style.display = 'none';
        }

        // Validación del formulario de creación de administrador
        document.addEventListener('DOMContentLoaded', function () {
            var formCrearAdministrador = document.getElementById('formCrearAdministrador');
            formCrearAdministrador.addEventListener('submit', function (event) {
                var password = document.getElementById('password').value;
                var confirmarPassword = document.getElementById('confirmar_password').value;

                if (password !== confirmarPassword) {
                    mostrarMensajeExito('Las contraseñas no coinciden');
                    event.preventDefault();
                    return;
                }

                // Comprobar si la cédula o celular ya existen
                var cedula = document.getElementById('cedula').value;
                var celular = document.getElementById('celular').value;
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "verificar_unicidad.php", false);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        var respuesta = JSON.parse(xhr.responseText);
                        if (respuesta.cedulaExistente || respuesta.celularExistente) {
                            mostrarMensajeExito('La cédula o el celular ya están registrados');
                            event.preventDefault();
                        }
                    }
                };
                xhr.send("cedula=" + cedula + "&celular=" + celular);
            });

            // Validación del formulario de edición de administrador
            var formEditarAdministrador = document.getElementById('formEditarAdministrador');
            formEditarAdministrador.addEventListener('submit', function (event) {
                var password = document.getElementById('editar_password').value;
                var confirmarPassword = document.getElementById('editar_confirmar_password').value;

                if (password && password !== confirmarPassword) {
                    mostrarMensajeExito('Las contraseñas no coinciden');
                    event.preventDefault();
                    return;
                }

                // Comprobar si la cédula o celular ya existen
                var cedula = document.getElementById('editar_cedula').value;
                var celular = document.getElementById('editar_celular').value;
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "verificar_unicidad.php", false);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        var respuesta = JSON.parse(xhr.responseText);
                        if (respuesta.cedulaExistente || respuesta.celularExistente) {
                            mostrarMensajeExito('La cédula o el celular ya están registrados');
                            event.preventDefault();
                        }
                    }
                };
                xhr.send("cedula=" + cedula + "&celular=" + celular + "&id=" + document.getElementById('editar_id').value);
            });

            var mensaje = "<?php echo $mensaje; ?>";
            if (mensaje) {
                mostrarMensajeExito(mensaje);
                setTimeout(function() {
                    window.location.href = 'administradores.php';
                }, 3000); // Espera 3 segundos y luego recarga la página sin el mensaje
            }
        });
    </script>
</head>
<body>
    <?php include '../inicio/menu.php'; ?>
    <main>
        <h2>Gestión de Administradores</h2>
        <button class="crear" onclick="mostrarFormularioCrear()">Crear Administrador</button>
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Cédula</th>
                    <th>Celular</th>
                    <th>Usuario</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['nombre']; ?></td>
                    <td><?php echo $row['apellido']; ?></td>
                    <td><?php echo $row['cedula']; ?></td>
                    <td><?php echo $row['celular']; ?></td>
                    <td><?php echo $row['username']; ?></td>
                    <td><?php echo $row['estado'] ? 'Activo' : 'Inactivo'; ?></td>
                    <td>
                        <button class="editar" onclick="mostrarFormularioEditar(<?php echo $row['id']; ?>)">Editar</button>
                        <button class="<?php echo $row['estado'] ? 'inactivar' : 'activar'; ?>" onclick="cambiarEstadoAdministrador(<?php echo $row['id']; ?>)">
                            <?php echo $row['estado'] ? 'Inactivar' : 'Activar'; ?>
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>
    <!-- Formulario modal para crear administrador -->
    <div id="modalCrear" class="modal">
        <div class="modal-contenido">
            <span class="cerrar" onclick="cerrarFormularioCrear()">&times;</span>
            <h2>Crear Administrador</h2>
            <form id="formCrearAdministrador" action="crear_administrador.php" method="post">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" required>
                </div>
                <div class="form-group">
                    <label for="cedula">Cédula:</label>
                    <input type="text" id="cedula" name="cedula" required>
                </div>
                <div class="form-group">
                    <label for="celular">Celular:</label>
                    <input type="text" id="celular" name="celular" required>
                </div>
                <div class="form-group">
                    <label for="username">Usuario:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirmar_password">Confirmar Contraseña:</label>
                    <input type="password" id="confirmar_password" name="confirmar_password" required>
                </div>
                <button type="submit">Crear Administrador</button>
            </form>
        </div>
    </div>

    <!-- Formulario modal para editar administrador -->
    <div id="modalEditar" class="modal">
        <div class="modal-contenido">
            <span class="cerrar" onclick="cerrarFormularioEditar()">&times;</span>
            <h2>Editar Administrador</h2>
            <form id="formEditarAdministrador" action="editar_administrador.php" method="post">
                <input type="hidden" id="editar_id" name="id">
                <div class="form-group">
                    <label for="editar_nombre">Nombre:</label>
                    <input type="text" id="editar_nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="editar_apellido">Apellido:</label>
                    <input type="text" id="editar_apellido" name="apellido" required>
                </div>
                <div class="form-group">
                    <label for="editar_cedula">Cédula:</label>
                    <input type="text" id="editar_cedula" name="cedula" required>
                </div>
                <div class="form-group">
                    <label for="editar_celular">Celular:</label>
                    <input type="text" id="editar_celular" name="celular" required>
                </div>
                <div class="form-group">
                    <label for="editar_username">Usuario:</label>
                    <input type="text" id="editar_username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="editar_password">Contraseña:</label>
                    <input type="password" id="editar_password" name="password">
                </div>
                <div class="form-group">
                    <label for="editar_confirmar_password">Confirmar Contraseña:</label>
                    <input type="password" id="editar_confirmar_password" name="confirmar_password">
                </div>
                <button type="submit">Actualizar Administrador</button>
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


