<?php
session_start();
require '../config/db.php';

// 1. Seguridad
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'administrador') { header("Location: ../login.php"); exit; }

// 2. Consultas de Estadísticas
// Total Usuarios
$total_users = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
// Total Alumnos
$total_alumnos = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='alumno'")->fetchColumn();
// Total Profes
$total_profes = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='profesor'")->fetchColumn();
// Total Cursos
$total_cursos = $pdo->query("SELECT COUNT(*) FROM cursos")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

    <div class="sidebar">
        <div class="logo mb-4"><i class="bi bi-shield-lock-fill"></i> AdminPanel</div>
        
        <a href="dashboard.php" class="active"><i class="bi bi-speedometer2"></i> <span>Inicio</span></a>
        <hr class="text-secondary mx-3 my-2">
        <a href="usuarios.php"><i class="bi bi-people-fill"></i> <span>Usuarios</span></a>
        <a href="cursos.php"><i class="bi bi-building"></i> <span>Cursos y Materias</span></a>
        <a href="asignacion.php"><i class="bi bi-diagram-3-fill"></i> <span>Carga Académica</span></a>
        
        <div class="mt-5">
            <a href="../logout.php" class="text-danger"><i class="bi bi-box-arrow-left"></i> <span>Cerrar Sesión</span></a>
        </div>
    </div>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold">Panel de Control</h2>
                <p class="text-muted">Gestión centralizada del colegio.</p>
            </div>
            <div class="text-end">
                <span class="badge bg-dark px-3 py-2">Versión 1.0</span>
            </div>
        </div>

        <div class="row mb-5">
            
            <div class="col-md-3">
                <div class="card stat-card bg-primary text-white h-100 p-3 shadow-lg border-0" style="background: linear-gradient(45deg, #1e3c72, #2a5298);">
                    <div class="card-body">
                        <h6 class="text-uppercase opacity-75 small fw-bold">Total Usuarios</h6>
                        <h2 class="display-4 fw-bold mb-0"><?php echo $total_users; ?></h2>
                        <i class="bi bi-people-fill stat-icon" style="opacity: 0.2;"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card bg-success text-white h-100 p-3 shadow-lg border-0" style="background: linear-gradient(45deg, #11998e, #38ef7d);">
                    <div class="card-body">
                        <h6 class="text-uppercase opacity-75 small fw-bold">Alumnos Activos</h6>
                        <h2 class="display-4 fw-bold mb-0"><?php echo $total_alumnos; ?></h2>
                        <i class="bi bi-backpack4-fill stat-icon" style="opacity: 0.2;"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card bg-info text-white h-100 p-3 shadow-lg border-0" style="background: linear-gradient(45deg, #3a7bd5, #00d2ff);">
                    <div class="card-body">
                        <h6 class="text-uppercase opacity-75 small fw-bold">Docentes</h6>
                        <h2 class="display-4 fw-bold mb-0"><?php echo $total_profes; ?></h2>
                        <i class="bi bi-person-video3 stat-icon" style="opacity: 0.2;"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card bg-warning text-white h-100 p-3 shadow-lg border-0" style="background: linear-gradient(45deg, #f7971e, #ffd200);">
                    <div class="card-body">
                        <h6 class="text-uppercase opacity-75 small fw-bold">Aulas / Cursos</h6>
                        <h2 class="display-4 fw-bold mb-0"><?php echo $total_cursos; ?></h2>
                        <i class="bi bi-building-fill stat-icon" style="opacity: 0.2;"></i>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="fw-bold text-secondary mb-3">Acciones Frecuentes</h5>
        <div class="row">
            <div class="col-md-4">
                <a href="usuarios.php" class="card shadow-sm text-decoration-none text-dark hover-effect h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="bg-light p-3 rounded-circle me-3 text-primary">
                            <i class="bi bi-person-plus-fill fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Crear Usuario</h6>
                            <small class="text-muted">Registrar nuevo alumno o profe</small>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="asignacion.php" class="card shadow-sm text-decoration-none text-dark hover-effect h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="bg-light p-3 rounded-circle me-3 text-danger">
                            <i class="bi bi-link-45deg fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Asignar Carga</h6>
                            <small class="text-muted">Vincular Profe + Materia</small>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="cursos.php" class="card shadow-sm text-decoration-none text-dark hover-effect h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="bg-light p-3 rounded-circle me-3 text-warning">
                            <i class="bi bi-calendar-plus fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Gestión de Cursos</h6>
                            <small class="text-muted">Crear 4°C, Talleres, etc.</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>

    </div>

</body>
</html>