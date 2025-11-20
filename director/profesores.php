<?php
session_start();
require '../config/db.php';
require '../includes/funciones.php';
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'director') { header("Location: ../login.php"); exit; }

// Obtener todos los profesores
$sqlProfes = "SELECT * FROM usuarios WHERE rol = 'profesor' ORDER BY nombre";
$profesores = $pdo->query($sqlProfes)->fetchAll();

// Función auxiliar para obtener las clases de un profe
function obtenerClases($pdo, $profe_id) {
    $sql = "SELECT c.nombre as curso, a.nombre as materia 
            FROM programacion_academica pa
            JOIN cursos c ON pa.curso_id = c.id
            JOIN asignaturas a ON pa.asignatura_id = a.id
            WHERE pa.profesor_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$profe_id]);
    return $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cuerpo Docente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

    <div class="sidebar">
        <div class="logo mb-4"><i class="bi bi-bank2"></i> Dirección</div>
        <a href="dashboard.php"><i class="bi bi-grid-fill"></i> <span>Menú Principal</span></a>
        <a href="resumen.php"><i class="bi bi-bar-chart-line-fill"></i> <span>Estadísticas</span></a>
        <div class="mt-5"><a href="../logout.php" class="text-danger"><i class="bi bi-box-arrow-left"></i> <span>Salir</span></a></div>
    </div>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-dark"><i class="bi bi-people-fill"></i> Cuerpo Docente</h3>
            <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill">Volver</a>
        </div>

        <div class="row">
            <?php if(count($profesores) == 0): ?>
                <div class="col-12"><div class="alert alert-info">No hay profesores registrados.</div></div>
            <?php else: ?>
                
                <?php foreach($profesores as $profe): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body text-center p-4">
                                <?php $foto = $profe['foto'] ? "../assets/uploads/perfiles/".$profe['foto'] : "https://cdn-icons-png.flaticon.com/512/2995/2995620.png"; ?>
                                <img src="<?php echo $foto; ?>" class="rounded-circle mb-3 border border-3 shadow-sm" width="100" height="100" style="object-fit: cover;">
                                
                                <h5 class="fw-bold mb-1"><?php echo $profe['nombre']; ?></h5>
                                <p class="text-muted small mb-3"><?php echo $profe['email']; ?></p>
                                
                                <hr class="opacity-10">
                                
                                <div class="text-start">
                                    <small class="fw-bold text-uppercase text-secondary" style="font-size: 0.7rem;">Carga Académica:</small>
                                    <ul class="list-unstyled mt-2 small">
                                        <?php 
                                        $clases = obtenerClases($pdo, $profe['id']);
                                        if(count($clases) > 0):
                                            foreach($clases as $c): 
                                        ?>
                                            <li class="mb-1"><i class="bi bi-book text-primary me-2"></i> <?php echo $c['materia']; ?> (<?php echo $c['curso']; ?>)</li>
                                        <?php endforeach; else: ?>
                                            <li class="text-muted fst-italic">Sin asignaciones.</li>
                                        <?php endif; ?>
                                    </ul>
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