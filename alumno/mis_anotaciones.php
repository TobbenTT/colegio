<?php
session_start();
require '../config/db.php';

// 1. Seguridad
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'alumno') { header("Location: ../login.php"); exit; }

$alumno_id = $_SESSION['user_id'];

// 2. Consultar anotaciones
$sql = "SELECT a.*, u.nombre as autor 
        FROM anotaciones a 
        INNER JOIN usuarios u ON a.autor_id = u.id 
        WHERE a.alumno_id = :id 
        ORDER BY a.fecha DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $alumno_id]);
$mis_notas = $stmt->fetchAll();

// Contadores rápidos para el resumen superior
$positivas = 0;
$negativas = 0;
foreach($mis_notas as $n) {
    if($n['tipo'] == 'positiva') $positivas++;
    else $negativas++;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Hoja de Vida</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/mis_anotaciones.css" rel="stylesheet">
</head>
<body>

    <div class="sidebar">
        <div class="logo mb-4"><i class="bi bi-backpack2-fill"></i> Mi Colegio</div>
        <a href="dashboard.php"><i class="bi bi-grid-fill"></i> <span>Mis Clases</span></a>
        <a href="horario.php"><i class="bi bi-calendar-week"></i> <span>Horario</span></a>
        <a href="asistencia.php"><i class="bi bi-clipboard-check"></i> <span>Asistencia</span></a>
        <a href="mis_anotaciones.php" class="active"><i class="bi bi-exclamation-triangle"></i> <span>Hoja de Vida</span></a>
        <a href="perfil.php"><i class="bi bi-person-circle"></i> <span>Mi Perfil</span></a>
        <div class="mt-5"><a href="../logout.php" class="text-danger"><i class="bi bi-box-arrow-left"></i> <span>Salir</span></a></div>
    </div>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark"><i class="bi bi-journal-richtext"></i> Hoja de Vida</h2>
                <p class="text-muted">Registro de comportamiento y observaciones.</p>
            </div>
            
            <div class="d-flex gap-3">
                <div class="text-center px-3 border-end">
                    <h3 class="fw-bold text-success mb-0"><?php echo $positivas; ?></h3>
                    <small class="text-muted text-uppercase" style="font-size: 0.7rem;">Méritos</small>
                </div>
                <div class="text-center px-3">
                    <h3 class="fw-bold text-danger mb-0"><?php echo $negativas; ?></h3>
                    <small class="text-muted text-uppercase" style="font-size: 0.7rem;">Faltas</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-10 mx-auto">
                
                <?php if(count($mis_notas) == 0): ?>
                    <div class="text-center py-5 mt-4">
                        <div class="mb-3 opacity-25">
                            <i class="bi bi-shield-check display-1 text-secondary"></i>
                        </div>
                        <h4 class="fw-bold text-secondary">Hoja de Vida Impecable</h4>
                        <p class="text-muted">No tienes anotaciones registradas hasta el momento.</p>
                    </div>
                <?php else: ?>
                    
                    <?php foreach($mis_notas as $nota): ?>
                        <?php 
                            $es_positiva = ($nota['tipo'] == 'positiva');
                            $clase_borde = $es_positiva ? 'note-positive' : 'note-negative';
                            $clase_badge = $es_positiva ? 'badge-pos' : 'badge-neg';
                            $texto_badge = $es_positiva ? 'Mérito / Positiva' : 'Falta / Negativa';
                            $icono_fondo = $es_positiva ? 'bi-trophy-fill' : 'bi-exclamation-triangle-fill';
                        ?>

                        <div class="card note-card <?php echo $clase_borde; ?> p-4">
                            
                            <i class="bi <?php echo $icono_fondo; ?> note-bg-icon <?php echo $es_positiva ? 'text-success' : 'text-danger'; ?>"></i>

                            <div class="position-relative"> <div class="note-header">
                                    <span class="note-badge <?php echo $clase_badge; ?>">
                                        <i class="bi <?php echo $es_positiva ? 'bi-check-circle-fill' : 'bi-x-circle-fill'; ?>"></i> 
                                        <?php echo $texto_badge; ?>
                                    </span>
                                    <small class="text-muted fw-bold">
                                        <i class="bi bi-calendar-event"></i> 
                                        <?php echo date("d/m/Y - H:i", strtotime($nota['fecha'])); ?>
                                    </small>
                                </div>

                                <h5 class="text-dark mb-3" style="line-height: 1.6;">
                                    <?php echo nl2br(htmlspecialchars($nota['detalle'])); ?>
                                </h5>

                                <div class="d-flex align-items-center mt-4 pt-3 border-top border-light">
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2 text-secondary" style="width: 35px; height: 35px;">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Registrado por:</small>
                                        <span class="fw-bold text-secondary small"><?php echo $nota['autor']; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </div>
    </div>

</body>
</html>