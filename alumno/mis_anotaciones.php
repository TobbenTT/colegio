<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'alumno') {
    header("Location: login.php"); exit;
}
$alumno_id = $_SESSION['user_id'];

// Consultar mis anotaciones
$sql = "SELECT a.*, u.nombre as autor 
        FROM anotaciones a 
        INNER JOIN usuarios u ON a.autor_id = u.id 
        WHERE a.alumno_id = :id 
        ORDER BY a.fecha DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $alumno_id]);
$mis_notas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Hoja de Vida</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Mi Hoja de Vida</h3>
            <a href="dashboard.php" class="btn btn-secondary">Volver al Inicio</a>
        </div>

        <?php if(count($mis_notas) == 0): ?>
            <div class="alert alert-success">¡Felicidades! No tienes anotaciones registradas.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach($mis_notas as $nota): ?>
                    <div class="col-md-12 mb-2">
                        <div class="card border-start border-5 <?php echo ($nota['tipo']=='positiva')?'border-success':'border-danger'; ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <h5 class="<?php echo ($nota['tipo']=='positiva')?'text-success':'text-danger'; ?>">
                                        <?php echo strtoupper($nota['tipo']); ?>
                                    </h5>
                                    <small class="text-muted"><?php echo date("d/m/Y H:i", strtotime($nota['fecha'])); ?></small>
                                </div>
                                <p class="card-text fs-5"><?php echo $nota['detalle']; ?></p>
                                <p class="card-text text-muted small">Escrito por: <?php echo $nota['autor']; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>