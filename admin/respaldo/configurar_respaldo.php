<?php
include '../conexion.php';
include '../inicio/menu.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ruta_respaldo = $_POST['ruta_respaldo'];
    $dias_semana = implode(',', $_POST['dias_semana']);
    $hora_respaldo = $_POST['hora_respaldo'];

    // Verificar si existe una configuración existente
    $sql = "SELECT COUNT(*) AS count FROM configuracion WHERE id = 1";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        // Actualizar la configuración existente
        $sql = "UPDATE configuracion SET ruta_respaldo = ?, dias_semana = ?, hora_respaldo = ? WHERE id = 1";
    } else {
        // Insertar una nueva configuración
        $sql = "INSERT INTO configuracion (ruta_respaldo, dias_semana, hora_respaldo) VALUES (?, ?, ?)";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $ruta_respaldo, $dias_semana, $hora_respaldo);
    $stmt->execute();
}

// Obtener configuración actual
$sql = "SELECT ruta_respaldo, dias_semana, hora_respaldo FROM configuracion WHERE id = 1";
$result = $conn->query($sql);
$configuracion = $result->fetch_assoc();
$dias_semana_seleccionados = isset($configuracion['dias_semana']) ? explode(',', $configuracion['dias_semana']) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Respaldo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        nav {
            background-color: #0d2c4d;
            overflow: hidden;
        }

        nav ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
            display: flex;
        }

        nav ul li {
            float: left;
        }

        nav ul li a {
            display: block;
            color: white;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }

        nav ul li a:hover {
            background-color: #111;
        }

        nav ul .dropdown {
            display: inline-block;
        }

        nav ul .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
        }

        nav ul .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        nav ul .dropdown-content a:hover {
            background-color: #f1f1f1;
        }

        nav ul .dropdown:hover .dropdown-content {
            display: block;
        }

        nav ul .dropdown:hover .dropbtn {
            background-color: #3e8e41;
        }

        .main {
            margin: 20px;
        }

        h1, h2 {
            color: #0d2c4d;
        }

        button {
            padding: 10px 15px;
            border: none;
            cursor: pointer;
        }

        button[type="submit"] {
            background-color: #0d2c4d;
            color: white;
        }

        button.realizar-respaldo {
            background-color: #28a745;
            color: white;
        }

        form {
            max-width: 600px;
            margin: 0 auto;
        }

        label {
            display: block;
            margin-bottom: 8px;
        }

        input[type="text"], input[type="time"], button {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .dias-semana label {
            display: inline-block;
            width: 100px;
        }

        .error {
            color: red;
            font-weight: bold;
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

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            text-align: center;
            border-radius: 8px;
        }

        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-left-color: #4caf50;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        .modal h2 {
            margin: 20px 0;
        }

        .countdown {
            font-size: 24px;
            color: #0d2c4d;
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="main">
        <h1>Configurar Respaldo</h1>
        <form method="post">
            <label for="ruta_respaldo">Ruta de Respaldo:</label>
            <input type="text" id="ruta_respaldo" name="ruta_respaldo" value="<?php echo isset($configuracion['ruta_respaldo']) ? htmlspecialchars($configuracion['ruta_respaldo']) : ''; ?>" required>
            
            <label>Días de la semana para el respaldo:</label>
            <div class="dias-semana">
                <?php
                $dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                foreach ($dias_semana as $dia) {
                    $checked = in_array($dia, $dias_semana_seleccionados) ? 'checked' : '';
                    echo "<label><input type='checkbox' name='dias_semana[]' value='$dia' $checked> $dia</label><br>";
                }
                ?>
            </div>
            
            <label for="hora_respaldo">Hora de Respaldo:</label>
            <input type="time" id="hora_respaldo" name="hora_respaldo" value="<?php echo isset($configuracion['hora_respaldo']) ? $configuracion['hora_respaldo'] : ''; ?>" required>
            
            <button type="submit">Guardar Configuración</button>
        </form>
        
        <button class="realizar-respaldo" onclick="realizarRespaldoManual()">Realizar Respaldo Ahora</button>
        <div id="mensajeError" class="error"></div>

        <div class="countdown">
            Falta <span id="countdown"></span> para la copia de seguridad.
        </div>
    </div>

    <div id="modalSpinner" class="modal">
        <div class="modal-content">
            <div class="spinner"></div>
            <h2 id="modalMessage">Realizando respaldo...</h2>
        </div>
    </div>

    <script>
        function realizarRespaldoManual() {
            var modal = document.getElementById('modalSpinner');
            var modalMessage = document.getElementById('modalMessage');
            modal.style.display = 'block';
            modalMessage.innerText = 'Eliminando copia de seguridad anterior...';

            fetch('respaldar.php?manual=1')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        modalMessage.innerText = data.message || 'Respaldo manual realizado con éxito.';
                        setTimeout(() => {
                            modal.style.display = 'none';
                        }, 3000);
                    } else {
                        modalMessage.innerText = data.error || 'Error al realizar el respaldo manual.';
                        setTimeout(() => {
                            modal.style.display = 'none';
                        }, 3000);
                    }
                })
                .catch(error => {
                    modalMessage.innerText = 'Error al realizar el respaldo manual: ' + error.message;
                    setTimeout(() => {
                        modal.style.display = 'none';
                    }, 3000);
                });
        }

        function verificarRespaldoAutomatico() {
            fetch('verificar_respaldo.php')
                .then(response => response.json())
                .then(data => {
                    if (data.ejecutar) {
                        realizarRespaldoManual();
                    }
                })
                .catch(error => {
                    console.error('Error al verificar el respaldo automático:', error);
                });
        }

        // Verificar respaldo automático cada minuto
        setInterval(verificarRespaldoAutomatico, 60000);

        // Iniciar la verificación al cargar la página
        document.addEventListener('DOMContentLoaded', verificarRespaldoAutomatico);

        function actualizarCuentaRegresiva() {
            var config = <?php echo json_encode($configuracion); ?>;
            var diasSemana = config.dias_semana.split(',');
            var horaRespaldo = config.hora_respaldo;

            var diasSemanaTraducidos = {
                'Lunes': 'Monday',
                'Martes': 'Tuesday',
                'Miércoles': 'Wednesday',
                'Jueves': 'Thursday',
                'Viernes': 'Friday',
                'Sábado': 'Saturday',
                'Domingo': 'Sunday'
            };

            var proximosDias = diasSemana.map(dia => diasSemanaTraducidos[dia]);

            var ahora = new Date();
            var diaActual = ahora.toLocaleString('en-US', { weekday: 'long' });

            var proximoDia = proximosDias.find(dia => dia >= diaActual) || proximosDias[0];
            var proximaFecha = new Date(ahora);
            proximaFecha.setDate(ahora.getDate() + ((7 + proximosDias.indexOf(proximoDia) - ahora.getDay()) % 7));
            proximaFecha.setHours(horaRespaldo.split(':')[0]);
            proximaFecha.setMinutes(horaRespaldo.split(':')[1]);
            proximaFecha.setSeconds(0);

            if (proximaFecha < ahora) {
                proximaFecha.setDate(proximaFecha.getDate() + 7);
            }

            var diferencia = proximaFecha - ahora;
            var horas = Math.floor(diferencia / 1000 / 60 / 60);
            var minutos = Math.floor((diferencia / 1000 / 60) % 60);
            var segundos = Math.floor((diferencia / 1000) % 60);

            function pad(value) {
                return String(value).padStart(2, '0');
            }

            document.getElementById('countdown').innerText = `${pad(horas)}:${pad(minutos)}:${pad(segundos)}`;
        }

        setInterval(actualizarCuentaRegresiva, 1000);
        actualizarCuentaRegresiva();
    </script>
</body>
</html>



