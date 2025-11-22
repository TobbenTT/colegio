<?php
require '../config/db.php';
header('Content-Type: application/json');

// Traer todos los eventos
$sql = "SELECT id, titulo as title, fecha_inicio as start, fecha_fin as end, color, descripcion FROM eventos";
$stmt = $pdo->query($sql);
$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($eventos);
?>