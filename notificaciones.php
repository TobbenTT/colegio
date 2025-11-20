<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$user_id = $_SESSION['user_id'];

// 1. MARCAR TODO COMO LEÍDO (Al entrar a esta página)
$pdo->prepare("UPDATE notificaciones SET leido = 1 WHERE usuario_id = ?")->execute([$user_id]);

// 2. OBTENER NOTIFICACIONES
$sql = "SELECT * FROM notificaciones WHERE usuario_id = ? ORDER BY fecha DESC LIMIT 50";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$notificaciones = $stmt->fetchAll();

// Determinar link de volver según rol
$rol = $_SESSION['rol'];
$volver = ($rol == 'administrador') ? 'admin/dashboard.php' : $rol.'/dashboard.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Notificaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-bold"><i class="bi bi-bell-fill text-warning"></i> Centro de Notificaciones</h3>
                    <a href="<?php echo $volver; ?>" class="btn btn-outline-secondary rounded-pill">Volver al Panel</a>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="list-group list-group-flush">
                        <?php if(count($notificaciones) == 0): ?>
                            <div class="p-5 text-center text-muted">
                                <i class="bi bi-check-circle display-1 opacity-25"></i>
                                <p class="mt-3">Estás al día. No tienes notificaciones.</p>
                            </div>
                        <?php else: ?>
                            
                            <?php foreach($notificaciones as $n): ?>
                                <?php 
                                    // Determinar carpeta destino (alumno, profesor, etc)
                                    // El enlace guardado puede ser relativo "ver_curso.php", hay que ajustarlo si estamos en root
                                    $link_final = ($n['enlace'] == '#') ? '#' : $_SESSION['rol'] . '/' . $n['enlace'];
                                    $bg_class = ($n['leido'] == 0) ? 'bg-white fw-bold border-start border-4 border-primary' : 'bg-light text-muted';
                                ?>
                                <a href="<?php echo $link_final; ?>" class="list-group-item list-group-item-action p-3 <?php echo $bg_class; ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo $n['mensaje']; ?></h6>
                                        <small class="<?php echo ($n['leido']==0)?'text-primary':'text-muted'; ?>">
                                            <?php echo date("d/m H:i", strtotime($n['fecha'])); ?>
                                        </small>
                                    </div>
                                    <?php if($n['enlace'] != '#'): ?>
                                        <small class="text-primary"><i class="bi bi-link-45deg"></i> Ver detalles</small>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>

                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>