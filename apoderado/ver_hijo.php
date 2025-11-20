<?php
session_start();
require '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'apoderado') { header("Location: ../login.php"); exit; }

$hijo_id = $_GET['id'];
$papa_id = $_SESSION['user_id'];

// 1. VALIDAR RELACIÓN (Seguridad Crítica)
$check = $pdo->prepare("SELECT id FROM familia WHERE apoderado_id = ? AND alumno_id = ?");
$check->execute([$papa_id, $hijo_id]);
if (!$check->fetch()) die("Acceso denegado.");

// 2. DATOS DEL HIJO
$alumno = $pdo->prepare("SELECT u.nombre, u.foto, c.nombre as curso 
                         FROM usuarios u 
                         JOIN matriculas m ON u.id = m.alumno_id 
                         JOIN cursos c ON m.curso_id = c.id 
                         WHERE u.id = ?");
$alumno->execute([$hijo_id]);
$datos_alumno = $alumno->fetch();

// 3. DATOS DE ASISTENCIA
$asist = $pdo->prepare("SELECT 
                            COUNT(*) as total, 
                            SUM(CASE WHEN estado='presente' THEN 1 ELSE 0 END) as presentes 
                        FROM asistencia WHERE alumno_id = ?");
$asist->execute([$hijo_id]);
$dato_asis = $asist->fetch();
$porcentaje = ($dato_asis['total']>0) ? round(($dato_asis['presentes']/$dato_asis['total'])*100) : 100;

// 4. ANOTACIONES RECIENTES
$notas_vida = $pdo->prepare("SELECT * FROM anotaciones WHERE alumno_id = ? ORDER BY fecha DESC LIMIT 5");
$notas_vida->execute([$hijo_id]);
$anotaciones = $notas_vida->fetchAll();

// 5. NOTAS Y PROMEDIOS
$promedios = $pdo->prepare("SELECT AVG(nota) as promedio FROM entregas WHERE alumno_id = ? AND nota > 0");
$promedios->execute([$hijo_id]);
$prom_general = number_format($promedios->fetchColumn(), 1);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resumen Académico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

    <div class="sidebar">
        <div class="logo mb-4"><i class="bi bi-people-fill"></i> Apoderados</div>
        <a href="dashboard.php" class="active"><i class="bi bi-grid-fill"></i> <span>Mis Pupilos</span></a>
        <div class="mt-5"><a href="dashboard.php" class="text-light"><i class="bi bi-arrow-left"></i> Volver</a></div>
    </div>

    <div class="main-content">
        
        <div class="card shadow-sm border-0 mb-4 bg-primary text-white">
            <div class="card-body p-4 d-flex align-items-center">
                <?php $foto = $datos_alumno['foto'] ? "../assets/uploads/perfiles/".$datos_alumno['foto'] : "https://cdn-icons-png.flaticon.com/512/2995/2995620.png"; ?>
                <img src="<?php echo $foto; ?>" class="rounded-circle border border-3 border-white me-4" width="80" height="80" style="object-fit:cover;">
                <div>
                    <h2 class="fw-bold mb-0"><?php echo $datos_alumno['nombre']; ?></h2>
                    <p class="mb-0 opacity-75">Curso: <?php echo $datos_alumno['curso']; ?></p>
                </div>
            </div>
        </div>

        <div class="row">
            
            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body text-center">
                        <h6 class="text-muted text-uppercase small fw-bold">Asistencia</h6>
                        <div class="position-relative d-inline-block my-3">
                            <h1 class="display-4 fw-bold <?php echo ($porcentaje<85)?'text-danger':'text-success'; ?>">
                                <?php echo $porcentaje; ?>%
                            </h1>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar <?php echo ($porcentaje<85)?'bg-danger':'bg-success'; ?>" style="width: <?php echo $porcentaje; ?>%"></div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body text-center">
                        <h6 class="text-muted text-uppercase small fw-bold">Promedio General</h6>
                        <h1 class="display-4 fw-bold text-primary"><?php echo $prom_general; ?></h1>
                        <small class="text-muted">Calculado en base a entregas calificadas</small>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-secondary">Últimas Anotaciones</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-modern table-hover mb-0">
                                <tbody>
                                    <?php if(count($anotaciones) == 0): ?>
                                        <tr><td class="text-center py-4 text-muted">Sin anotaciones registradas.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($anotaciones as $nota): ?>
                                            <tr>
                                                <td class="ps-4 text-center" style="width: 50px;">
                                                    <?php if($nota['tipo']=='positiva'): ?>
                                                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                                    <?php else: ?>
                                                        <i class="bi bi-exclamation-triangle-fill text-danger fs-4"></i>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-dark d-block">
                                                        <?php echo ucfirst($nota['tipo']); ?>
                                                    </span>
                                                    <small class="text-muted"><?php echo date("d/m/Y", strtotime($nota['fecha'])); ?></small>
                                                </td>
                                                <td class="text-muted small pe-4">
                                                    <?php echo $nota['detalle']; ?>
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
    </div>

</body>
</html>