<?php
session_start();
require '../config/db.php';

// IMPORTANTE: Configura tu zona horaria para que coincida con la hora real de la clase
date_default_timezone_set('America/Santiago'); 

header('Content-Type: application/json');

if (!isset($_POST['id_alumno'])) {
    echo json_encode(['status' => 'error', 'msg' => 'No se recibió ID']);
    exit;
}

$alumno_id = $_POST['id_alumno'];
$fecha_hoy = date('Y-m-d');
$hora_actual = date('H:i:s');

// Traducción del día actual al español (para coincidir con tu BD)
$dias_en = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$dias_es = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
$dia_hoy = str_replace($dias_en, $dias_es, date('l'));

// 1. OBTENER DATOS DEL ALUMNO
$stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ? AND rol = 'alumno'");
$stmt->execute([$alumno_id]);
$alumno = $stmt->fetch();

if (!$alumno) {
    echo json_encode(['status' => 'error', 'msg' => 'Alumno no encontrado']);
    exit;
}

// 2. LÓGICA INTELIGENTE: BUSCAR LA CLASE ACTUAL
// Buscamos en la tabla HORARIOS una clase que:
// a) Sea del día de hoy.
// b) La hora actual esté entre el inicio y el fin.
// c) Corresponda al curso donde el alumno está matriculado.

$sqlClase = "SELECT 
                h.id as horario_id,
                h.programacion_id,
                a.nombre as materia,
                c.nombre as curso
             FROM horarios h
             JOIN programacion_academica pa ON h.programacion_id = pa.id
             JOIN asignaturas a ON pa.asignatura_id = a.id
             JOIN cursos c ON pa.curso_id = c.id
             JOIN matriculas m ON c.id = m.curso_id
             WHERE m.alumno_id = :alumno_id
             AND h.dia = :dia
             AND :hora BETWEEN h.hora_inicio AND h.hora_fin
             LIMIT 1";

$stmtClase = $pdo->prepare($sqlClase);
$stmtClase->execute([
    'alumno_id' => $alumno_id,
    'dia' => $dia_hoy,
    'hora' => $hora_actual
]);

$clase_actual = $stmtClase->fetch();

if ($clase_actual) {
    // ¡ENCONTRAMOS LA CLASE!
    $prog_id = $clase_actual['programacion_id'];
    $horario_id = $clase_actual['horario_id'];
    $nombre_materia = $clase_actual['materia'];

    // 3. EVITAR DUPLICADOS (Si ya pasó el QR hace 5 minutos)
    $check = $pdo->prepare("SELECT id FROM asistencia WHERE alumno_id = ? AND fecha = ? AND horario_id = ?");
    $check->execute([$alumno_id, $fecha_hoy, $horario_id]);
    
    if(!$check->fetch()) {
        // 4. REGISTRAR ASISTENCIA
        $sqlIns = "INSERT INTO asistencia (programacion_id, alumno_id, horario_id, fecha, estado) 
                   VALUES (?, ?, ?, ?, 'presente')";
        
        if($pdo->prepare($sqlIns)->execute([$prog_id, $alumno_id, $horario_id, $fecha_hoy])){
            echo json_encode([
                'status' => 'success', 
                'nombre' => $alumno['nombre'], 
                'msg' => "Presente en $nombre_materia"
            ]);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Error al guardar']);
        }
    } else {
        // Ya estaba presente
        echo json_encode([
            'status' => 'warning', 
            'nombre' => $alumno['nombre'],
            'msg' => "Ya registrado en $nombre_materia"
        ]);
    }

} else {
    // NO HAY CLASE AHORA
    // Puede ser recreo, o el alumno no tiene clases a esta hora
    echo json_encode([
        'status' => 'error', 
        'msg' => "Sin clases asignadas ahora ($dia_hoy $hora_actual)"
    ]);
}
?>