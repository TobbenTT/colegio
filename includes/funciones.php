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
?>