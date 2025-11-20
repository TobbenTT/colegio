<?php
session_start();
require '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'director') { header("Location: login.php"); exit; }

// 1. KPIs
$total_alumnos = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'alumno'")->fetchColumn();
$total_profes = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'profesor'")->fetchColumn();
$pos = $pdo->query("SELECT COUNT(*) FROM anotaciones WHERE tipo='positiva'")->fetchColumn();
$neg = $pdo->query("SELECT COUNT(*) FROM anotaciones WHERE tipo='negativa'")->fetchColumn();

// 2. Actividad
$actividad_reciente = $pdo->query("SELECT a.titulo, u.nombre as profe FROM actividades a JOIN programacion_academica pa ON a.programacion_id = pa.id JOIN usuarios u ON pa.profesor_id = u.id ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resumen General</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
    <div class="container mt-4">
        <a href="dashboard.php" class="btn btn-secondary mb-3">&larr; Volver al Menú</a>
        <h2>Resumen de Estadísticas</h2>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card p-3">
                    <canvas id="graficoConducta"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                 <ul class="list-group">
                    <li class="list-group-item active">Últimas Actividades</li>
                    <?php foreach($actividad_reciente as $act): ?>
                        <li class="list-group-item"><?php echo $act['titulo'] . " (" . $act['profe'] . ")"; ?></li>
                    <?php endforeach; ?>
                 </ul>
            </div>
        </div>
    </div>
    <script>
        // Script del gráfico del paso anterior
        const ctx = document.getElementById('graficoConducta').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Positivas', 'Negativas'],
                datasets: [{ data: [<?php echo $pos; ?>, <?php echo $neg; ?>], backgroundColor: ['#198754', '#dc3545'] }]
            }
        });
    </script>
</body>
</html>