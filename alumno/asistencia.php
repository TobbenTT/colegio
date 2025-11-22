<?php
session_start();
require '../config/db.php';
require '../includes/funciones.php';
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'alumno') { header("Location: ../login.php"); exit; }

$alumno_id = $_SESSION['user_id'];

// Estadísticas
$sqlStats = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'presente' THEN 1 ELSE 0 END) as presentes,
                SUM(CASE WHEN estado = 'ausente' THEN 1 ELSE 0 END) as ausentes,
                SUM(CASE WHEN estado = 'atrasado' THEN 1 ELSE 0 END) as atrasos
             FROM asistencia WHERE alumno_id = ?";
$stmtStats = $pdo->prepare($sqlStats);
$stmtStats->execute([$alumno_id]);
$stats = $stmtStats->fetch();

$porcentaje = ($stats['total'] > 0) ? round(($stats['presentes'] / $stats['total']) * 100) : 100;
$color_clase = ($porcentaje < 85) ? 'bg-danger' : 'bg-success';
$text_color = ($porcentaje < 85) ? 'text-danger' : 'text-success';

// Historial
$sqlDetalle = "SELECT asis.fecha, asis.estado, a.nombre as materia 
               FROM asistencia asis
               JOIN programacion_academica pa ON asis.programacion_id = pa.id
               JOIN asignaturas a ON pa.asignatura_id = a.id
               WHERE asis.alumno_id = ? ORDER BY asis.fecha DESC";
$stmtDet = $pdo->prepare($sqlDetalle);
$stmtDet->execute([$alumno_id]);
$historial = $stmtDet->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Asistencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

<?php include '../includes/sidebar_alumno.php'; ?>

    <div class="main-content">
        <h2 class="fw-bold mb-4">📊 Reporte de Asistencia</h2>

        <div class="row">
            <div class="col-md-5 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center p-5">
                        <h5 class="text-muted text-uppercase small fw-bold">Porcentaje Global</h5>
                        <h1 class="display-1 fw-bold <?php echo $text_color; ?> mb-0"><?php echo $porcentaje; ?>%</h1>
                        
                        <div class="progress mt-3 mb-4" style="height: 15px; border-radius: 10px;">
                            <div class="progress-bar <?php echo $color_clase; ?>" style="width: <?php echo $porcentaje; ?>%"></div>
                        </div>

                        <div class="d-flex justify-content-around mt-4">
                            <div class="text-center">
                                <h3 class="fw-bold text-success mb-0"><?php echo $stats['presentes']; ?></h3>
                                <small class="text-muted">Presentes</small>
                            </div>
                            <div class="text-center">
                                <h3 class="fw-bold text-warning mb-0"><?php echo $stats['atrasos']; ?></h3>
                                <small class="text-muted">Atrasos</small>
                            </div>
                            <div class="text-center">
                                <h3 class="fw-bold text-danger mb-0"><?php echo $stats['ausentes']; ?></h3>
                                <small class="text-muted">Faltas</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">Historial de Clases</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-modern table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Fecha</th>
                                        <th>Materia</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($historial as $fila): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-secondary">
                                                <?php echo date("d/m", strtotime($fila['fecha'])); ?>
                                            </td>
                                            <td><?php echo $fila['materia']; ?></td>
                                            <td>
                                                <?php if($fila['estado'] == 'presente'): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Presente</span>
                                                <?php elseif($fila['estado'] == 'atrasado'): ?>
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3">Atrasado</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">Ausente</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>