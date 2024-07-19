<?php
// archivo: gestion/usuarios/usuarios.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include '../conexion.php';

// Obtener el filtro si existe
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : '';

// Obtener los usuarios con filtro
$sql = "
    SELECT u.*, GROUP_CONCAT(d.nombre SEPARATOR ', ') as dependencias
    FROM usuarios u
    LEFT JOIN usuarios_dependencias ud ON u.id = ud.usuario_id
    LEFT JOIN dependencia d ON ud.dependencia_id = d.id
    WHERE u.nombres LIKE ? OR u.apellidos LIKE ? OR u.cedula LIKE ? OR u.celular LIKE ?
    GROUP BY u.id
    LIMIT 15";
$stmt = $conn->prepare($sql);
$filtro_param = "%$filtro%";
$stmt->bind_param("ssss", $filtro_param, $filtro_param, $filtro_param, $filtro_param);
$stmt->execute();
$result = $stmt->get_result();

// Obtener las dependencias
$sql_dep = "SELECT * FROM dependencia";
$dependencias = $conn->query($sql_dep)->fetch_all(MYSQLI_ASSOC);

// Obtener el mensaje de éxito si existe
$mensaje = isset($_GET['mensaje']) ? $_GET['mensaje'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Gestión</title>
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
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            flex: 0 0 48%;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="password"],
        .form-group input[type="date"],
        .form-group select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        .dependencias-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .dependencias-container label {
            flex: 0 0 48%;
            text-align: left;
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
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Ajusta el tamaño del modal */
            max-width: 800px;
            text-align: center; /* Centra el contenido del modal */
            border-radius: 8px; /* Añade bordes redondeados */
        }

        .modal-contenido h2 {
            margin: 0 0 20px 0;
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

        .form-group div {
            flex: 0 0 48%;
        }

        .full-width {
            flex: 0 0 100%;
        }

        .search-container {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .search-container input[type="text"] {
            flex: 0 0 70%;
            padding: 8px;
            margin-right: 10px;
            box-sizing: border-box;
        }

        .search-container button {
            flex: 0 0 25%;
            background-color: #28a745;
            color: white;
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
            // Cargar los datos del usuario desde el servidor y rellenar el formulario
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "obtener_usuario.php?id=" + id, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var usuario = JSON.parse(xhr.responseText);
                    document.getElementById('editar_id').value = usuario.id;
                    document.getElementById('editar_nombres').value = usuario.nombres;
                    document.getElementById('editar_apellidos').value = usuario.apellidos;
                    document.getElementById('editar_cedula').value = usuario.cedula;
                    document.getElementById('editar_celular').value = usuario.celular;
                    document.getElementById('editar_fecha_nacimiento').value = usuario.fecha_nacimiento;
                    document.getElementById('editar_username').value = usuario.username;
                    document.getElementById('editar_estado').value = usuario.estado;

                    // Marcar las dependencias seleccionadas
                    var dependencias = usuario.dependencias;
                    var checkboxes = document.querySelectorAll('#formEditarUsuario input[name="editar_dependencia[]"]');
                    checkboxes.forEach(function(checkbox) {
                        checkbox.checked = dependencias.includes(parseInt(checkbox.value));
                    });

                    document.getElementById('modalEditar').style.display = 'block';
                }
            };
            xhr.send();
        }

        function cerrarFormularioEditar() {
            document.getElementById('modalEditar').style.display = 'none';
        }

        function filtrarUsuarios() {
            var filtro = document.getElementById('filtro').value;

            // Redirigir con el filtro aplicado
            window.location.href = 'usuarios.php?filtro=' + encodeURIComponent(filtro);
        }

        function cambiarEstadoUsuario(id) {
            // Realizar la solicitud al servidor para cambiar el estado del usuario
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "cambiar_estado_usuario.php", true);
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

        // Validación del formulario de creación de usuario
        document.addEventListener('DOMContentLoaded', function () {
            var formCrearUsuario = document.getElementById('formCrearUsuario');
            formCrearUsuario.addEventListener('submit', function (event) {
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
                xhr.open("POST", "verificar_unicidad_usuario.php", false);
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

            // Validación del formulario de edición de usuario
            var formEditarUsuario = document.getElementById('formEditarUsuario');
            formEditarUsuario.addEventListener('submit', function (event) {
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
                xhr.open("POST", "verificar_unicidad_usuario.php", false);
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
                    window.location.href = 'usuarios.php';
                }, 3000); // Espera 3 segundos y luego recarga la página sin el mensaje
            }
        });
    </script>
</head>
<body>
    <?php include '../inicio/menu.php'; ?>
    <main>
        <h2>Gestión de Usuarios</h2>
        <div class="search-container">
            <input type="text" id="filtro" placeholder="Buscar por nombre, cédula o celular" value="<?php echo htmlspecialchars($filtro); ?>">
            <button class="filtrar" onclick="filtrarUsuarios()">Buscar</button>
        </div>
        <button class="crear" onclick="mostrarFormularioCrear()">Crear Usuario</button>
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Nombres</th>
                    <th>Apellidos</th>
                    <th>Cédula</th>
                    <th>Celular</th>
                    <th>Fecha de Nacimiento</th>
                    <th>Usuario</th>
                    <th>Estado</th>
                    <th>Dependencias</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['nombres']; ?></td>
                    <td><?php echo $row['apellidos']; ?></td>
                    <td><?php echo $row['cedula']; ?></td>
                    <td><?php echo $row['celular']; ?></td>
                    <td><?php echo $row['fecha_nacimiento']; ?></td>
                    <td><?php echo $row['username']; ?></td>
                    <td><?php echo $row['estado'] ? 'Activo' : 'Inactivo'; ?></td>
                    <td><?php echo $row['dependencias']; ?></td>
                    <td>
                        <button class="editar" onclick="mostrarFormularioEditar(<?php echo $row['id']; ?>)">Editar</button>
                        <button class="<?php echo $row['estado'] ? 'inactivar' : 'activar'; ?>" onclick="cambiarEstadoUsuario(<?php echo $row['id']; ?>)">
                            <?php echo $row['estado'] ? 'Inactivar' : 'Activar'; ?>
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>
    <!-- Formulario modal para crear usuario -->
    <div id="modalCrear" class="modal">
        <div class="modal-contenido">
            <span class="cerrar" onclick="cerrarFormularioCrear()">&times;</span>
            <h2>Crear Usuario</h2>
            <form id="formCrearUsuario" action="crear_usuario.php" method="post">
                <div class="form-group">
                    <div>
                        <label for="nombres">Nombres:</label>
                        <input type="text" id="nombres" name="nombres" required>
                    </div>
                    <div>
                        <label for="apellidos">Apellidos:</label>
                        <input type="text" id="apellidos" name="apellidos" required>
                    </div>
                </div>
                <div class="form-group">
                    <div>
                        <label for="cedula">Cédula:</label>
                        <input type="text" id="cedula" name="cedula" required>
                    </div>
                    <div>
                        <label for="celular">Celular:</label>
                        <input type="text" id="celular" name="celular" required>
                    </div>
                </div>
                <div class="form-group">
                    <div>
                        <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>
                    </div>
                </div>
                <div class="form-group">
                    <div>
                        <label for="username">Usuario:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div>
                        <label for="password">Contraseña:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                </div>
                <div class="form-group">
                    <div>
                        <label for="confirmar_password">Confirmar Contraseña:</label>
                        <input type="password" id="confirmar_password" name="confirmar_password" required>
                    </div>
                    <div>
                        <label for="estado">Estado:</label>
                        <select id="estado" name="estado" required>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="form-group full-width">
                    <label for="dependencia">Dependencia:</label>
                    <div class="dependencias-container">
                        <?php foreach ($dependencias as $row_dep): ?>
                            <label>
                                <input type="checkbox" name="dependencia[]" value="<?php echo $row_dep['id']; ?>">
                                <?php echo $row_dep['nombre']; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="submit">Crear Usuario</button>
            </form>
        </div>
    </div>

    <!-- Formulario modal para editar usuario -->
    <div id="modalEditar" class="modal">
        <div class="modal-contenido">
            <span class="cerrar" onclick="cerrarFormularioEditar()">&times;</span>
            <h2>Editar Usuario</h2>
            <form id="formEditarUsuario" action="editar_usuario.php" method="post">
                <input type="hidden" id="editar_id" name="id">
                <div class="form-group">
                    <div>
                        <label for="editar_nombres">Nombres:</label>
                        <input type="text" id="editar_nombres" name="nombres" required>
                    </div>
                    <div>
                        <label for="editar_apellidos">Apellidos:</label>
                        <input type="text" id="editar_apellidos" name="apellidos" required>
                    </div>
                </div>
                <div class="form-group">
                    <div>
                        <label for="editar_cedula">Cédula:</label>
                        <input type="text" id="editar_cedula" name="cedula" required>
                    </div>
                    <div>
                        <label for="editar_celular">Celular:</label>
                        <input type="text" id="editar_celular" name="celular" required>
                    </div>
                </div>
                <div class="form-group">
                    <div>
                        <label for="editar_fecha_nacimiento">Fecha de Nacimiento:</label>
                        <input type="date" id="editar_fecha_nacimiento" name="fecha_nacimiento" required>
                    </div>
                </div>
                <div class="form-group">
                    <div>
                        <label for="editar_username">Usuario:</label>
                        <input type="text" id="editar_username" name="username" required>
                    </div>
                    <div>
                        <label for="editar_password">Contraseña:</label>
                        <input type="password" id="editar_password" name="password">
                    </div>
                </div>
                <div class="form-group">
                    <div>
                        <label for="editar_confirmar_password">Confirmar Contraseña:</label>
                        <input type="password" id="editar_confirmar_password" name="confirmar_password">
                    </div>
                    <div>
                        <label for="editar_estado">Estado:</label>
                        <select id="editar_estado" name="estado" required>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="form-group full-width">
                    <label for="editar_dependencia">Dependencia:</label>
                    <div class="dependencias-container">
                        <?php foreach ($dependencias as $row_dep): ?>
                            <label>
                                <input type="checkbox" name="editar_dependencia[]" value="<?php echo $row_dep['id']; ?>">
                                <?php echo $row_dep['nombre']; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="submit">Actualizar Usuario</button>
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











