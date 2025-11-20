<?php
session_start();
require '../config/db.php';

// 1. Seguridad
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'apoderado') { header("Location: ../login.php"); exit; }

$apoderado_id = $_SESSION['user_id'];

// 2. Obtener Hijos
$sql = "SELECT u.id, u.nombre, u.foto, c.nombre as curso 
        FROM familia f
        JOIN usuarios u ON f.alumno_id = u.id
        JOIN matriculas m ON u.id = m.alumno_id
        JOIN cursos c ON m.curso_id = c.id
        WHERE f.apoderado_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$apoderado_id]);
$hijos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Portal Apoderado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

<?php include '../includes/sidebar_apoderado.php'; ?>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold">Hola, <?php echo explode(" ", $_SESSION['nombre'])[0]; ?></h2>
                <p class="text-muted">Selecciona un estudiante para ver su situación.</p>
            </div>
        </div>

        <div class="row">
            <?php if(count($hijos) == 0): ?>
                <div class="col-12">
                    <div class="alert alert-warning shadow-sm border-0">
                        <i class="bi bi-exclamation-circle-fill me-2"></i>
                        No tienes estudiantes asociados. Contacta a secretaría.
                    </div>
                </div>
            <?php else: ?>
                
                <?php foreach($hijos as $hijo): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow border-0 text-center hover-effect">
                            <div class="card-body p-5">
                                <?php 
                                    $foto = $hijo['foto'] ? "../assets/uploads/perfiles/".$hijo['foto'] : "https://cdn-icons-png.flaticon.com/512/2995/2995620.png"; 
                                ?>
                                <img src="<?php echo $foto; ?>" class="rounded-circle mb-3 border border-4 border-white shadow" width="120" height="120" style="object-fit: cover;">
                                
                                <h4 class="fw-bold text-dark mb-1"><?php echo $hijo['nombre']; ?></h4>
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 mb-4">
                                    <?php echo $hijo['curso']; ?>
                                </span>
                                
                                <div class="d-grid">
                                    <a href="ver_hijo.php?id=<?php echo $hijo['id']; ?>" class="btn btn-primary rounded-pill fw-bold">
                                        Ver Rendimiento <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php endif; ?>
        </div>

    </div>

</body>
</html>