<?php
session_start();
require '../config/db.php';
require '../includes/funciones.php';

// 1. Seguridad
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'profesor') { header("Location: ../login.php"); exit; }

$profe_id = $_SESSION['user_id'];

// 2. Obtener Cursos del Profesor
$sql = "SELECT 
            pa.id as prog_id,
            c.nombre as curso,
            a.nombre as materia
        FROM programacion_academica pa
        JOIN cursos c ON pa.curso_id = c.id
        JOIN asignaturas a ON pa.asignatura_id = a.id
        WHERE pa.profesor_id = :pid";
$stmt = $pdo->prepare($sql);
$stmt->execute(['pid' => $profe_id]);
$clases = $stmt->fetchAll();

// 3. Estadísticas Rápidas
$total_cursos = count($clases);

// Contar total de alumnos únicos a los que les hace clases
$sqlAlumnos = "SELECT COUNT(DISTINCT m.alumno_id) 
               FROM matriculas m 
               JOIN programacion_academica pa ON m.curso_id = pa.curso_id 
               WHERE pa.profesor_id = ?";
$stmtAlu = $pdo->prepare($sqlAlumnos);
$stmtAlu->execute([$profe_id]);
$total_alumnos = $stmtAlu->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Docente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

<?php include '../includes/sidebar_profesor.php'; ?>
    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold">Bienvenido, Profe <?php echo explode(" ", $_SESSION['nombre'])[0]; ?> 👋</h2>
                <p class="text-muted">Panel de gestión académica.</p>
            </div>
            <?php 
                $avatar = isset($_SESSION['foto']) && !empty($_SESSION['foto']) ? "../assets/uploads/perfiles/".$_SESSION['foto'] : "https://cdn-icons-png.flaticon.com/512/2995/2995620.png";
            ?>
            <img src="<?php echo $avatar; ?>" width="50" height="50" class="rounded-circle border shadow-sm" style="object-fit:cover;">
        </div>
        <?php include '../includes/widget_anuncios.php'; ?>

        <div class="row mb-5">
            <div class="col-md-6">
                <div class="card stat-card bg-gradient-primary h-100 p-3">
                    <div class="card-body">
                        <h5 class="card-title opacity-75">Cursos Asignados</h5>
                        <h2 class="display-4 fw-bold"><?php echo $total_cursos; ?></h2>
                        <i class="bi bi-easel2-fill stat-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card stat-card bg-gradient-success h-100 p-3">
                    <div class="card-body">
                        <h5 class="card-title opacity-75">Total Alumnos</h5>
                        <h2 class="display-4 fw-bold"><?php echo $total_alumnos; ?></h2>
                        <i class="bi bi-people-fill stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <h4 class="fw-bold mb-3 text-secondary">Tus Aulas Virtuales</h4>
        <div class="row">
            <?php if($total_cursos == 0): ?>
                <div class="col-12"><div class="alert alert-info">No tienes cursos asignados aún.</div></div>
            <?php else: ?>
                <?php foreach($clases as $clase): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card course-card-student h-100 shadow-sm border-0">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="icon-wrapper bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:50px; height:50px;">
                                        <i class="bi bi-book fs-4"></i>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3"><?php echo $clase['curso']; ?></span>
                                </div>
                                
                                <h4 class="fw-bold mb-1"><?php echo $clase['materia']; ?></h4>
                                <p class="text-muted small mb-4">Gestión de contenidos y notas.</p>
                                
                                <a href="ver_curso.php?id=<?php echo $clase['prog_id']; ?>" class="btn btn-primary w-100 fw-bold py-2 rounded-3">
                                    Entrar al Aula <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>