<?php
session_start();
require '../config/db.php';

// 1. Seguridad: Si no es profe, lo echamos
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'profesor') {
    header("Location: login.php");
    exit;
}

$profesor_id = $_SESSION['user_id'];

// 2. La Consulta Mágica
// Buscamos en la tabla 'programacion_academica' todas las filas donde el profesor sea EL QUE ESTÁ LOGUEADO.
// Usamos JOIN para traer el nombre del curso (ej: 4°A) y el nombre de la asignatura (ej: Matemáticas)
$sql = "SELECT 
            pa.id as programacion_id,
            c.nombre as nombre_curso,
            a.nombre as nombre_asignatura
        FROM programacion_academica pa
        INNER JOIN cursos c ON pa.curso_id = c.id
        INNER JOIN asignaturas a ON pa.asignatura_id = a.id
        WHERE pa.profesor_id = :profe_id";

$stmt = $pdo->prepare($sql);
$stmt->execute(['profe_id' => $profesor_id]);
$clases = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Profesor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

    <div class="sidebar">
        <div class="logo">
            <i class="bi bi-mortarboard-fill"></i> ColegioApp
        </div>
        
        <a href="dashboard.php" class="active"><i class="bi bi-speedometer2"></i> <span>Mis Cursos</span></a>
        <a href="perfil.php"><i class="bi bi-person-circle"></i> <span>Mi Perfil</span></a>
        <a href="#" class="text-muted small text-uppercase px-4 mt-3 mb-2">Herramientas</a>
        <a href="mensajes.php"><i class="bi bi-chat-dots"></i> <span>Mensajería</span></a>
        
        <div style="margin-top: 50px;">
            <a href="../logout.php" class="text-danger"><i class="bi bi-box-arrow-left"></i> <span>Cerrar Sesión</span></a>
        </div>
    </div>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold">Hola, <?php echo $_SESSION['nombre']; ?> 👋</h2>
                <p class="text-muted">Bienvenido a tu panel docente.</p>
            </div>
            <div>
                <span class="badge bg-primary p-2 rounded-pill">Profesor</span>
            </div>
        </div>

        <h4 class="mb-3">Tus Asignaturas Activas</h4>
        <div class="row">
            <?php if (count($clases) > 0): ?>
                <?php foreach ($clases as $clase): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between mb-3">
                                    <div class="icon-box bg-light text-primary rounded p-3">
                                        <i class="bi bi-book fs-2"></i>
                                    </div>
                                    <div class="text-end">
                                        <h5 class="fw-bold mb-0"><?php echo $clase['nombre_curso']; ?></h5>
                                        <small class="text-muted">2025</small>
                                    </div>
                                </div>
                                <h4 class="card-title mb-3"><?php echo $clase['nombre_asignatura']; ?></h4>
                                <p class="card-text text-muted small">Gestiona asistencia, notas y material.</p>
                                
                                <a href="ver_curso.php?id=<?php echo $clase['programacion_id']; ?>" class="btn btn-primary w-100 mt-auto">
                                    Entrar al Aula <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">No tienes cursos asignados.</div>
                </div>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>