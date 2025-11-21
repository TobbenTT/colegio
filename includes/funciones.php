<?php
// includes/funciones.php

function enviarNotificacion($pdo, $usuario_id, $mensaje, $enlace = '#') {
    $sql = "INSERT INTO notificaciones (usuario_id, mensaje, enlace) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$usuario_id, $mensaje, $enlace]);
}

function contarNotificacionesNoLeidas($pdo, $usuario_id) {
    $sql = "SELECT COUNT(*) FROM notificaciones WHERE usuario_id = ? AND leido = 0";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);
    return $stmt->fetchColumn();
}

// FUNCION DE SEGURIDAD (XSS)
// Úsala siempre que imprimas texto: echo e($texto);
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// FUNCIÓN PARA CALCULAR PROMEDIO PONDERADO
// Calcula la nota real basada en los porcentajes
function calcularPromedioPonderado($pdo, $alumno_id, $curso_id) {
    // Buscamos solo las actividades que YA tienen nota puesta
    $sql = "SELECT e.nota, a.porcentaje 
            FROM entregas e
            JOIN actividades a ON e.actividad_id = a.id
            JOIN programacion_academica pa ON a.programacion_id = pa.id
            WHERE e.alumno_id = ? AND pa.curso_id = ? AND e.nota > 0";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$alumno_id, $curso_id]);
    $notas = $stmt->fetchAll();

    $suma_ponderada = 0;
    $suma_porcentajes = 0;

    foreach ($notas as $row) {
        $suma_ponderada += ($row['nota'] * $row['porcentaje']);
        $suma_porcentajes += $row['porcentaje'];
    }

    if ($suma_porcentajes > 0) {
        return number_format($suma_ponderada / $suma_porcentajes, 1);
    }
    return '0.0';
}
?>

