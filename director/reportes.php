<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'director') { header("Location: ../login.php"); exit; }

// VARIABLES DE CONTROL DE VISTA
$vista = 'cursos'; // por defecto
$curso_id = $_GET['curso_id'] ?? null;
$alumno_id = $_GET['alumno_id'] ?? null;

if ($alumno_id) {
    $vista = 'reporte_alumno';
} elseif ($curso_id) {
    $vista = 'lista_alumnos';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes Académicos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <style>
        @media print {
            /* 1. Ocultar elementos de navegación y botones */
            .sidebar, 
            .no-print, 
            .btn, 
            header, 
            .mobile-nav-toggle { /* <--- ESTO OCULTA EL BOTÓN FLOTANTE */
                display: none !important;
            }

            /* 2. Resetear el contenedor principal */
            .main-content { 
                margin: 0 !important; 
                padding: 0 !important; 
                width: 100% !important; 
            }

            /* 3. Limpiar la tarjeta para que parezca una hoja */
            .card { 
                border: none !important; 
                box-shadow: none !important; 
                padding: 0 !important; /* Quitamos padding del card para controlarlo en @page */
            }

            body { 
                background-color: white; 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact;
            }

            /* 4. TRUCO PARA QUITAR "LOCALHOST" Y FECHA AUTOMÁTICA */
            @page {
                margin: 0; /* Esto elimina los encabezados y pies de página del navegador */
                size: auto;
            }

            /* 5. Crear márgenes seguros para el contenido impreso */
            #print-area {
                margin: 1.5cm !important; /* Margen físico en la hoja */
                padding: 0 !important;
            }
        }

        .hover-card:hover { transform: translateY(-3px); cursor: pointer; border-color: var(--accent-color); }
    </style>
</head>
<body>

    <?php include '../includes/sidebar_director.php'; ?>

    <div class="main-content">
        
        <?php if ($vista == 'cursos'): ?>
            <?php 
                $sql = "SELECT c.*, COUNT(m.id) as total_alumnos 
                        FROM cursos c 
                        LEFT JOIN matriculas m ON c.id = m.curso_id 
                        GROUP BY c.id ORDER BY c.nombre";
                $cursos = $pdo->query($sql)->fetchAll();
            ?>
            
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <h3 class="fw-bold"><i class="bi bi-building"></i> Seleccionar Curso</h3>
                <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill">Volver al Menú</a>
            </div>

            <div class="row no-print">
                <?php foreach($cursos as $c): ?>
                    <div class="col-md-4 mb-4">
                        <a href="reportes.php?curso_id=<?php echo $c['id']; ?>" class="text-decoration-none text-dark">
                            <div class="card h-100 shadow-sm border hover-card">
                                <div class="card-body text-center p-4">
                                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                        <i class="bi bi-people-fill"></i>
                                    </div>
                                    <h4 class="fw-bold"><?php echo $c['nombre']; ?></h4>
                                    <p class="text-muted mb-0"><?php echo $c['total_alumnos']; ?> Estudiantes Matriculados</p>
                                </div>
                                <div class="card-footer bg-light text-center border-0">
                                    <small class="fw-bold text-primary">Ver Alumnos <i class="bi bi-arrow-right"></i></small>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php elseif ($vista == 'lista_alumnos'): ?>
            <?php 
                $stmtC = $pdo->prepare("SELECT nombre FROM cursos WHERE id = ?");
                $stmtC->execute([$curso_id]);
                $nombre_curso = $stmtC->fetchColumn();

                $sql = "SELECT u.id, u.nombre, u.email, u.foto 
                        FROM matriculas m 
                        JOIN usuarios u ON m.alumno_id = u.id 
                        WHERE m.curso_id = ? 
                        ORDER BY u.nombre";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$curso_id]);
                $alumnos = $stmt->fetchAll();
            ?>

            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <div>
                    <h3 class="fw-bold mb-0">Curso: <?php echo $nombre_curso; ?></h3>
                    <p class="text-muted">Seleccione un estudiante para generar su reporte.</p>
                </div>
                <a href="reportes.php" class="btn btn-outline-secondary rounded-pill"><i class="bi bi-arrow-left"></i> Volver a Cursos</a>
            </div>

            <div class="card shadow-sm border-0 no-print">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Estudiante</th>
                                <th>Correo</th>
                                <th class="text-end pe-4">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($alumnos as $alu): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <?php $foto = $alu['foto'] ? "../assets/uploads/perfiles/".$alu['foto'] : "https://cdn-icons-png.flaticon.com/512/2995/2995620.png"; ?>
                                            <img src="<?php echo $foto; ?>" width="40" height="40" class="rounded-circle border me-3">
                                            <span class="fw-bold text-dark"><?php echo $alu['nombre']; ?></span>
                                        </div>
                                    </td>
                                    <td class="text-muted"><?php echo $alu['email']; ?></td>
                                    <td class="text-end pe-4">
                                        <a href="reportes.php?alumno_id=<?php echo $alu['id']; ?>&curso_id=<?php echo $curso_id; ?>" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">
                                            <i class="bi bi-file-pdf"></i> Generar Reporte
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($vista == 'reporte_alumno'): ?>
            <?php 
                // Consultas de datos (Igual que antes)
                $sqlInfo = "SELECT u.nombre, u.email, u.foto, c.nombre as curso FROM usuarios u JOIN matriculas m ON u.id = m.alumno_id JOIN cursos c ON m.curso_id = c.id WHERE u.id = ?";
                $stmt = $pdo->prepare($sqlInfo); $stmt->execute([$alumno_id]); $alumno = $stmt->fetch();

                $sqlAsis = "SELECT COUNT(*) as total, SUM(CASE WHEN estado='presente' THEN 1 ELSE 0 END) as presentes FROM asistencia WHERE alumno_id = ?";
                $stmt = $pdo->prepare($sqlAsis); $stmt->execute([$alumno_id]); $asis = $stmt->fetch();
                $pct_asis = ($asis['total'] > 0) ? round(($asis['presentes']/$asis['total'])*100) : 100;

                $sqlProm = "SELECT AVG(nota) FROM entregas WHERE alumno_id = ? AND nota > 0";
                $stmt = $pdo->prepare($sqlProm); $stmt->execute([$alumno_id]); $promedio = number_format($stmt->fetchColumn() ?: 0, 1);

                $sqlNotas = "SELECT a.nombre as materia, AVG(e.nota) as promedio_materia FROM programacion_academica pa JOIN asignaturas a ON pa.asignatura_id = a.id JOIN actividades act ON pa.id = act.programacion_id LEFT JOIN entregas e ON act.id = e.actividad_id AND e.alumno_id = ? WHERE pa.curso_id = (SELECT curso_id FROM matriculas WHERE alumno_id = ?) GROUP BY a.id";
                $stmt = $pdo->prepare($sqlNotas); $stmt->execute([$alumno_id, $alumno_id]); $notas_detalle = $stmt->fetchAll();

                $sqlAnot = "SELECT tipo, detalle, fecha FROM anotaciones WHERE alumno_id = ? ORDER BY fecha DESC";
                $stmt = $pdo->prepare($sqlAnot); $stmt->execute([$alumno_id]); $anotaciones = $stmt->fetchAll();
            ?>

            <div class="no-print mb-4 d-flex justify-content-between align-items-center">
                <a href="reportes.php?curso_id=<?php echo $_GET['curso_id']; ?>" class="btn btn-outline-secondary rounded-pill">
                    <i class="bi bi-arrow-left"></i> Volver a la lista
                </a>
                <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 fw-bold shadow">
                    <i class="bi bi-printer-fill"></i> Imprimir / Guardar PDF
                </button>
            </div>

            <div class="card shadow-sm border p-5 bg-white" id="print-area">
                
                <div class="d-flex justify-content-between border-bottom pb-4 mb-4">
                    <div class="d-flex align-items-center">
                        <img src="https://cdn-icons-png.flaticon.com/512/167/167707.png" width="60" class="me-3">
                        <div>
                            <h2 class="fw-bold mb-0 text-dark">Informe Académico</h2>
                            <p class="text-muted mb-0">Colegio Institucional | Año Escolar <?php echo date('Y'); ?></p>
                        </div>
                    </div>
                    <div class="text-end">
                        <p class="mb-1 fw-bold">Fecha de emisión:</p>
                        <p class="text-muted mb-0"><?php echo date('d/m/Y'); ?></p>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-2 text-center">
                        <?php $foto = $alumno['foto'] ? "../assets/uploads/perfiles/".$alumno['foto'] : "https://cdn-icons-png.flaticon.com/512/2995/2995620.png"; ?>
                        <img src="<?php echo $foto; ?>" width="100" height="100" class="border p-1" style="object-fit:cover;">
                    </div>
                    <div class="col-md-10">
                        <h3 class="fw-bold"><?php echo $alumno['nombre']; ?></h3>
                        <p class="mb-1"><strong>Curso:</strong> <?php echo $alumno['curso']; ?></p>
                        <p class="mb-1"><strong>Correo:</strong> <?php echo $alumno['email']; ?></p>
                    </div>
                </div>

                <div class="row text-center mb-5">
                    <div class="col-6 border-end">
                        <h6 class="text-uppercase text-muted small fw-bold">Promedio General</h6>
                        <h1 class="display-4 fw-bold text-primary"><?php echo $promedio; ?></h1>
                    </div>
                    <div class="col-6">
                        <h6 class="text-uppercase text-muted small fw-bold">Asistencia Total</h6>
                        <h1 class="display-4 fw-bold <?php echo ($pct_asis<85)?'text-danger':'text-success'; ?>">
                            <?php echo $pct_asis; ?>%
                        </h1>
                    </div>
                </div>

                <h5 class="fw-bold text-secondary border-bottom pb-2 mb-3">Desglose de Calificaciones</h5>
                <table class="table table-bordered mb-5">
                    <thead class="table-light">
                        <tr>
                            <th>Asignatura</th>
                            <th class="text-center" style="width: 150px;">Promedio</th>
                            <th class="text-center" style="width: 150px;">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($notas_detalle as $n): ?>
                            <?php 
                                $nota_final = $n['promedio_materia'] ? number_format($n['promedio_materia'], 1) : '-';
                                $aprobado = ($nota_final >= 4.0);
                            ?>
                            <tr>
                                <td><?php echo $n['materia']; ?></td>
                                <td class="text-center fw-bold"><?php echo $nota_final; ?></td>
                                <td class="text-center">
                                    <?php if($nota_final == '-') echo '<span class="text-muted">-</span>'; 
                                          elseif($aprobado) echo '<span class="text-success fw-bold">Aprobado</span>';
                                          else echo '<span class="text-danger fw-bold">Reprobado</span>';
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <h5 class="fw-bold text-secondary border-bottom pb-2 mb-3">Observaciones de Conducta</h5>
                <?php if(count($anotaciones) == 0): ?>
                    <p class="text-muted fst-italic">Sin observaciones registradas.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach($anotaciones as $anot): ?>
                            <li class="list-group-item px-0">
                                <span class="badge <?php echo ($anot['tipo']=='positiva')?'bg-success':'bg-danger'; ?> me-2">
                                    <?php echo ucfirst($anot['tipo']); ?>
                                </span>
                                <small class="text-muted me-2"><?php echo date('d/m/Y', strtotime($anot['fecha'])); ?>:</small>
                                <span><?php echo $anot['detalle']; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <div class="mt-5 pt-5 text-center">
                    <div class="border-top w-50 mx-auto pt-2">
                        <small class="text-muted">Firma Dirección / Timbre</small>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>