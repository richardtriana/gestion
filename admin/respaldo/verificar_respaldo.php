<?php
include '../conexion.php';

header('Content-Type: application/json');

session_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

$response = ['ejecutar' => false];

try {
    $sql = "SELECT ruta_respaldo, dias_semana, hora_respaldo FROM configuracion WHERE id = 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $configuracion = $result->fetch_assoc();
        $dias_semana = explode(',', $configuracion['dias_semana']);
        $hora_respaldo = $configuracion['hora_respaldo'];

        $dia_actual = date('l');
        $hora_actual = date('H:i:s');

        $dias_semana_traducidos = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado',
            'Sunday' => 'Domingo'
        ];

        $dia_actual_traducido = $dias_semana_traducidos[$dia_actual];

        // Verificar si ya se realizó un respaldo correcto hoy
        $sql_respaldo = "SELECT COUNT(*) as count FROM respaldo WHERE estado = 'correcto' AND DATE(fecha_inicio) = CURDATE()";
        $result_respaldo = $conn->query($sql_respaldo);
        $row_respaldo = $result_respaldo->fetch_assoc();

        if ($row_respaldo['count'] == 0 && in_array($dia_actual_traducido, $dias_semana) && $hora_actual >= $hora_respaldo) {
            $response['ejecutar'] = true;
        }
    }

    echo json_encode($response);
    exit;
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode($response);
    exit;
}
?>


