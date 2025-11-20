<?php
session_start();
require '../config/db.php';
require '../includes/funciones.php';
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'director') { header("Location: ../login.php"); exit; }

// --- 1. CONSULTAS DE ESTADÍSTICAS (KPIs) ---

// Total Alumnos
$total_alumnos = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='alumno'")->fetchColumn();

// Asistencia Global Promedio
$sqlAsis = "SELECT AVG(CASE WHEN estado='presente' THEN 100 ELSE 0 END) as promedio FROM asistencia";
$asis_global = round($pdo->query($sqlAsis)->fetchColumn() ?: 0);

// Anotaciones (Para el Gráfico)
$negativas = $pdo->query("SELECT COUNT(*) FROM anotaciones WHERE tipo='negativa'")->fetchColumn();
$positivas = $pdo->query("SELECT COUNT(*) FROM anotaciones WHERE tipo='positiva'")->fetchColumn();

// Últimas 5 Alertas (Anotaciones Negativas)
$sqlAlertas = "SELECT a.detalle, a.fecha, u.nombre as alumno 
               FROM anotaciones a 
               JOIN usuarios u ON a.alumno_id = u.id 
               WHERE a.tipo = 'negativa' 
               ORDER BY a.fecha DESC LIMIT 5";
$alertas = $pdo->query($sqlAlertas)->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resumen Global</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <div class="sidebar">
        <div class="logo mb-4"><i class="bi bi-bank2"></i> Dirección</div>
        <a href="dashboard.php"><i class="bi bi-grid-fill"></i> <span>Menú Principal</span></a>
        <a href="resumen.php" class="active"><i class="bi bi-bar-chart-line-fill"></i> <span>Estadísticas</span></a>
        <div class="mt-5"><a href="../logout.php" class="text-danger"><i class="bi bi-box-arrow-left"></i> <span>Salir</span></a></div>
    </div>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark">Tablero de Control</h3>
                <p class="text-muted">Métricas clave del establecimiento.</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-left"></i> Volver al Menú
            </a>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stat-card text-white h-100 p-3 shadow border-0" style="background: linear-gradient(45deg, #11998e, #38ef7d);">
                    <div class="card-body">
                        <h6 class="opacity-75 text-uppercase small fw-bold">Matrícula Total</h6>
                        <h2 class="display-4 fw-bold mb-0"><?php echo $total_alumnos; ?></h2>
                        <i class="bi bi-people-fill stat-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card text-white h-100 p-3 shadow border-0" style="background: linear-gradient(45deg, #f7971e, #ffd200);">
                    <div class="card-body">
                        <h6 class="opacity-75 text-uppercase small fw-bold">Asistencia Global</h6>
                        <h2 class="display-4 fw-bold mb-0"><?php echo $asis_global; ?>%</h2>
                        <i class="bi bi-graph-up-arrow stat-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card text-white h-100 p-3 shadow border-0" style="background: linear-gradient(45deg, #ff416c, #ff4b2b);">
                    <div class="card-body">
                        <h6 class="opacity-75 text-uppercase small fw-bold">Alertas Conducta</h6>
                        <h2 class="display-4 fw-bold mb-0"><?php echo $negativas; ?></h2>
                        <i class="bi bi-exclamation-triangle stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-secondary">Balance de Convivencia</h5>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div style="width: 70%;">
                            <canvas id="graficoConducta"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-secondary">Últimos Incidentes</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Alumno</th>
                                    <th>Fecha</th>
                                    <th>Observación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($alertas) == 0): ?>
                                    <tr><td colspan="3" class="text-center py-4 text-muted">Sin novedades negativas.</td></tr>
                                <?php else: ?>
                                    <?php foreach($alertas as $a): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-dark"><?php echo $a['alumno']; ?></td>
                                            <td class="text-muted small"><?php echo date("d/m", strtotime($a['fecha'])); ?></td>
                                            <td>
                                                <span class="d-inline-block text-truncate text-danger small" style="max-width: 180px;">
                                                    <?php echo $a['detalle']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        const ctx = document.getElementById('graficoConducta').getContext('2d');
        const pos = <?php echo $positivas; ?>;
        const neg = <?php echo $negativas; ?>;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Méritos (Positivas)', 'Faltas (Negativas)'],
                datasets: [{
                    data: [pos, neg],
                    backgroundColor: ['#2ecc71', '#e74c3c'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>