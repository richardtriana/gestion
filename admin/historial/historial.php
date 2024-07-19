<?php
// archivo: admin/historial/historial.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../conexion.php';

// Obtener los filtros
$filtro_archivo = isset($_GET['archivo']) ? $_GET['archivo'] : '';
$filtro_accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$filtro_usuario = isset($_GET['usuario']) ? $_GET['usuario'] : '';
$filtro_desde = isset($_GET['desde']) ? $_GET['desde'] : '';
$filtro_hasta = isset($_GET['hasta']) ? $_GET['hasta'] : '';

// Construir la consulta con los filtros
$sql = "
    SELECT h.*, u.nombres, u.apellidos 
    FROM historial h 
    JOIN usuarios u ON h.usuario_id = u.id 
    WHERE h.nombre_archivo LIKE ? 
    AND h.accion LIKE ? 
    AND (u.nombres LIKE ? OR u.apellidos LIKE ?) 
    AND h.fecha BETWEEN ? AND ?
    ORDER BY h.fecha DESC";
$stmt = $conn->prepare($sql);
$filtro_archivo_like = "%" . $filtro_archivo . "%";
$filtro_accion_like = "%" . $filtro_accion . "%";
$filtro_usuario_like = "%" . $filtro_usuario . "%";
$filtro_desde = $filtro_desde ?: '1970-01-01';
$filtro_hasta = $filtro_hasta ?: '9999-12-31';
$stmt->bind_param('ssssss', $filtro_archivo_like, $filtro_accion_like, $filtro_usuario_like, $filtro_usuario_like, $filtro_desde, $filtro_hasta);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Acciones - Gestión</title>
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

        .full-width {
            flex: 0 0 100%;
        }

        .search-container {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .search-container label {
            margin-right: 10px;
            font-weight: bold;
        }

        .search-container input[type="text"],
        .search-container input[type="date"],
        .search-container select {
            flex: 0 0 15%;
            padding: 8px;
            margin-right: 10px;
            box-sizing: border-box;
        }

        .search-container button {
            flex: 0 0 10%;
            background-color: #28a745;
            color: white;
        }

        .search-container button.limpiar {
            background-color: #dc3545;
        }
    </style>
    <script>
        function mostrarHistorial(id) {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "obtener_historial.php?id=" + id, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var historial = JSON.parse(xhr.responseText);
                    var modal = document.getElementById('modalHistorial');
                    var historialContenido = document.getElementById('historialContenido');
                    historialContenido.innerHTML = '';

                    historial.forEach(function(accion) {
                        var row = document.createElement('tr');
                        row.innerHTML = '<td>' + accion.fecha + '</td><td>' + accion.accion + '</td><td>' + accion.nombre_archivo + '</td><td>' + accion.localizacion + '</td><td>' + accion.descripcion + '</td>';
                        historialContenido.appendChild(row);
                    });

                    modal.style.display = 'block';
                }
            };
            xhr.send();
        }

        function cerrarHistorial() {
            document.getElementById('modalHistorial').style.display = 'none';
        }
    </script>
</head>
<body>
    <?php include '../inicio/menu.php'; ?>
    <main>
        <h2>Historial de Acciones</h2>
        <div class="search-container">
            <form action="historial.php" method="get">
                <label for="archivo">Archivo:</label>
                <input type="text" id="archivo" name="archivo" placeholder="Archivo" value="<?php echo htmlspecialchars($filtro_archivo); ?>">
                <label for="accion">Acción:</label>
                <select id="accion" name="accion">
                    <option value="">Acción</option>
                    <option value="abrir" <?php echo $filtro_accion == 'abrir' ? 'selected' : ''; ?>>Abrir</option>
                    <option value="eliminar" <?php echo $filtro_accion == 'eliminar' ? 'selected' : ''; ?>>Eliminar</option>
                    <option value="renombrar" <?php echo $filtro_accion == 'renombrar' ? 'selected' : ''; ?>>Renombrar</option>
                    <!-- Agregar otras acciones según sea necesario -->
                </select>
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" placeholder="Usuario" value="<?php echo htmlspecialchars($filtro_usuario); ?>">
                <label for="desde">Desde:</label>
                <input type="date" id="desde" name="desde" value="<?php echo htmlspecialchars($filtro_desde !== '1970-01-01' ? $filtro_desde : ''); ?>">
                <label for="hasta">Hasta:</label>
                <input type="date" id="hasta" name="hasta" value="<?php echo htmlspecialchars($filtro_hasta !== '9999-12-31' ? $filtro_hasta : ''); ?>">
                <button type="submit">Buscar</button>
                <button type="button" class="limpiar" onclick="window.location.href='historial.php'">Limpiar</button>
            </form>
        </div>
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Archivo</th>
                    <th>Localización</th>
                    <th>Descripción</th>
                    <th>Fecha</th>
                    <th>Detalles</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nombres'] . ' ' . $row['apellidos']); ?></td>
                    <td><?php echo htmlspecialchars($row['accion']); ?></td>
                    <td><?php echo htmlspecialchars($row['nombre_archivo']); ?></td>
                    <td><?php echo htmlspecialchars($row['localizacion']); ?></td>
                    <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                    <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                    <td>
                        <button onclick="mostrarHistorial(<?php echo $row['id']; ?>)">Ver Detalles</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>

    <!-- Modal de historial -->
    <div id="modalHistorial" class="modal">
        <div class="modal-contenido">
            <span class="cerrar" onclick="cerrarHistorial()">&times;</span>
            <h2>Historial de Archivo o Carpeta</h2>
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Acción</th>
                        <th>Archivo</th>
                        <th>Localización</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody id="historialContenido">
                    <!-- Aquí se llenará con el historial específico -->
                </tbody>
            </table>
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

