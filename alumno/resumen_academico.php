<?php
session_start();
require '../config/db.php';
require '../includes/funciones.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'alumno') { header("Location: ../login.php"); exit; }

$alumno_id = $_SESSION['user_id'];

// 1. OBTENER CURSO DEL ALUMNO
$stmtCurso = $pdo->prepare("SELECT curso_id FROM matriculas WHERE alumno_id = ?");
$stmtCurso->execute([$alumno_id]);
$curso_id = $stmtCurso->fetchColumn();

if (!$curso_id) die("No tienes curso asignado.");

// 2. OBTENER MATERIAS Y PROFESORES
$sqlMaterias = "SELECT pa.id as prog_id, a.nombre as materia, u.nombre as profe, u.email as email_profe, u.foto
                FROM programacion_academica pa
                JOIN asignaturas a ON pa.asignatura_id = a.id
                JOIN usuarios u ON pa.profesor_id = u.id
                WHERE pa.curso_id = ?";
$stmtMat = $pdo->prepare($sqlMaterias);
$stmtMat->execute([$curso_id]);
$materias = $stmtMat->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resumen Académico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        /* Estilos tipo Intranet */
        .accordion-button:not(.collapsed) {
            background-color: #f8f9fa;
            color: var(--primary-color);
            box-shadow: inset 0 -1px 0 rgba(0,0,0,.125);
        }
        .accordion-button { font-weight: 600; }
        .progress { height: 10px; border-radius: 5px; background-color: #e9ecef; }
        .nota-box { 
            font-size: 1.2rem; 
            font-weight: bold; 
            min-width: 50px; 
            text-align: center;
        }
        .nota-roja { color: #dc3545; }
        .nota-azul { color: #0d6efd; }
        
        .teacher-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        
        /* Ocultar flecha de acordeon por defecto y ponerla a la derecha si quieres */
        /* .accordion-button::after { margin-left: auto; } */
    </style>
</head>
<body>

    <?php include '../includes/sidebar_alumno.php'; ?>

    <div class="main-content">
        
        <h3 class="fw-bold mb-4"><i class="bi bi-mortarboard-fill"></i> Situación Académica</h3>

        <div class="accordion shadow-sm" id="accordionAcademic">
            
            <?php foreach($materias as $index => $mat): ?>
                <?php 
                    // --- CÁLCULOS POR MATERIA ---
                    
                    // A. Asistencia
                    $sqlAsis = "SELECT COUNT(*) as total, SUM(CASE WHEN estado='presente' THEN 1 ELSE 0 END) as presentes 
                                FROM asistencia WHERE alumno_id = ? AND programacion_id = ?";
                    $stmtA = $pdo->prepare($sqlAsis);
                    $stmtA->execute([$alumno_id, $mat['prog_id']]);
                    $asis = $stmtA->fetch();
                    $pct_asis = ($asis['total'] > 0) ? round(($asis['presentes']/$asis['total'])*100) : 100;
                    $color_bar = ($pct_asis < 85) ? 'bg-danger' : 'bg-success';

                    // B. Notas y Evaluaciones
                    // Traemos todas las actividades tipo TAREA o PRUEBA
                    $sqlNotas = "SELECT act.titulo, act.porcentaje, act.fecha_limite, e.nota 
                                 FROM actividades act
                                 LEFT JOIN entregas e ON act.id = e.actividad_id AND e.alumno_id = ?
                                 WHERE act.programacion_id = ? AND act.tipo IN ('tarea', 'prueba')
                                 ORDER BY act.fecha_limite ASC";
                    $stmtN = $pdo->prepare($sqlNotas);
                    $stmtN->execute([$alumno_id, $mat['prog_id']]);
                    $evaluaciones = $stmtN->fetchAll();

                    // Calcular Promedio Ponderado
                    $suma_ponderada = 0;
                    $suma_porcentajes = 0;
                    $prox_evaluacion = "No hay pendientes";
                    
                    foreach($evaluaciones as $eva) {
                        if ($eva['nota'] > 0) {
                            $suma_ponderada += ($eva['nota'] * $eva['porcentaje']);
                            $suma_porcentajes += $eva['porcentaje'];
                        }
                        // Detectar próxima evaluación
                        if (strtotime($eva['fecha_limite']) > time() && $prox_evaluacion == "No hay pendientes") {
                            $dias = ceil((strtotime($eva['fecha_limite']) - time()) / 86400);
                            $prox_evaluacion = "Próxima: " . $eva['titulo'] . " (en $dias días)";
                        }
                    }

                    $promedio = ($suma_porcentajes > 0) ? number_format($suma_ponderada / $suma_porcentajes, 1) : '-';
                    $color_nota = ($promedio != '-' && $promedio < 4.0) ? 'nota-roja' : 'nota-azul';
                ?>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button <?php echo ($index>0)?'collapsed':''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $mat['prog_id']; ?>">
                            <div class="container-fluid p-0">
                                <div class="row align-items-center w-100">
                                    
                                    <div class="col-md-4">
                                        <div class="fw-bold text-dark"><?php echo $mat['materia']; ?></div>
                                        <small class="text-muted" style="font-size: 0.75rem;"><?php echo $mat['prog_id']; ?>-TI</small>
                                    </div>

                                    <div class="col-md-2 text-center border-start border-end">
                                        <small class="text-muted d-block text-uppercase" style="font-size: 0.65rem;">Promedio</small>
                                        <span class="nota-box <?php echo $color_nota; ?>"><?php echo $promedio; ?></span>
                                    </div>

                                    <div class="col-md-3 px-3">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-muted">Asistencia</span>
                                            <span class="fw-bold"><?php echo $pct_asis; ?>%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar <?php echo $color_bar; ?>" style="width: <?php echo $pct_asis; ?>%"></div>
                                        </div>
                                        <small class="text-muted" style="font-size: 0.65rem;"><?php echo $asis['presentes']; ?> hrs presentes / <?php echo $asis['total']; ?> total</small>
                                    </div>

                                    <div class="col-md-3 d-flex align-items-center border-start ps-3">
                                        <?php $foto = $mat['foto'] ? "../assets/uploads/perfiles/".$mat['foto'] : "https://cdn-icons-png.flaticon.com/512/2995/2995620.png"; ?>
                                        <img src="<?php echo $foto; ?>" class="teacher-avatar me-2">
                                        <div style="line-height: 1.1;">
                                            <div class="small fw-bold text-truncate" style="max-width: 130px;"><?php echo $mat['profe']; ?></div>
                                            <a href="mailto:<?php echo $mat['email_profe']; ?>" class="small text-primary text-decoration-none" style="font-size: 0.7rem;">Contactar</a>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </button>
                    </h2>
                    
                    <div id="collapse<?php echo $mat['prog_id']; ?>" class="accordion-collapse collapse <?php echo ($index==0)?'show':''; ?>" data-bs-parent="#accordionAcademic">
                        <div class="accordion-body bg-light">
                            
                            <?php if($prox_evaluacion != "No hay pendientes"): ?>
                                <div class="alert alert-warning py-2 px-3 small d-inline-block mb-3 shadow-sm">
                                    <i class="bi bi-clock-history"></i> <?php echo $prox_evaluacion; ?>
                                </div>
                            <?php endif; ?>

                            <div class="card border-0 shadow-sm">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 text-center small">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-start ps-4">Evaluación</th>
                                                <th>Ponderación</th>
                                                <th>Fecha</th>
                                                <th>Nota</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(count($evaluaciones) == 0): ?>
                                                <tr><td colspan="5" class="py-3 text-muted">No hay evaluaciones registradas.</td></tr>
                                            <?php else: ?>
                                                <?php foreach($evaluaciones as $eva): ?>
                                                    <?php 
                                                        $tiene_nota = ($eva['nota'] > 0);
                                                        $nota_fmt = $tiene_nota ? $eva['nota'] : '-';
                                                        $clase_n = ($tiene_nota && $eva['nota'] < 4.0) ? 'text-danger fw-bold' : 'text-primary fw-bold';
                                                    ?>
                                                    <tr>
                                                        <td class="text-start ps-4 fw-bold text-secondary"><?php echo $eva['titulo']; ?></td>
                                                        <td><?php echo $eva['porcentaje']; ?>%</td>
                                                        <td><?php echo $eva['fecha_limite'] ? date("d/m/Y", strtotime($eva['fecha_limite'])) : '-'; ?></td>
                                                        <td class="<?php echo $clase_n; ?> fs-6"><?php echo $nota_fmt; ?></td>
                                                        <td>
                                                            <?php if($tiene_nota): ?>
                                                                <span class="badge bg-success-subtle text-success">Calificado</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-light text-muted border">Pendiente</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                        <tfoot class="table-light fw-bold">
                                            <tr>
                                                <td colspan="3" class="text-end pe-3">Promedio Ponderado Actual:</td>
                                                <td class="<?php echo $color_nota; ?> fs-6"><?php echo $promedio; ?></td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="text-end mt-3">
                                <button onclick="window.print()" class="btn btn-dark btn-sm rounded-pill px-4">
                                    <i class="bi bi-printer"></i> Imprimir Informe
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>