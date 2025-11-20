<?php
require '../config/db.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'administrador') {
    header("Location: login.php"); exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración IT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-dark"> <nav class="navbar navbar-dark bg-black mb-5">
        <div class="container">
            <span class="navbar-brand text-warning">SISTEMA ADMINISTRATIVO</span>
            <span class="text-white">Hola, <?php echo $_SESSION['nombre']; ?> | <a href="../logout.php" class="text-white fw-bold">Salir</a></span>
        </div>
    </nav>

    <div class="container">
        <h2 class="text-white text-center mb-5">Configuración del Colegio</h2>
        
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <h1 class="text-success"><i class="bi bi-people-fill"></i></h1>
                        <h5 class="card-title">Usuarios</h5>
                        <p class="text-muted">Crear Profesores, Alumnos y Directivos.</p>
                        <a href="usuarios.php" class="btn btn-success w-100">Gestionar</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <h1 class="text-warning"><i class="bi bi-building-fill"></i></h1>
                        <h5 class="card-title">Cursos y Materias</h5>
                        <p class="text-muted">Crear aulas (4°A) y asignaturas.</p>
                        <a href="cursos.php" class="btn btn-warning w-100">Gestionar</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <h1 class="text-danger"><i class="bi bi-diagram-3-fill"></i></h1>
                        <h5 class="card-title">Carga Académica</h5>
                        <p class="text-muted">Unir Profesor + Curso + Materia.</p>
                        <a href="asignacion.php" class="btn btn-danger w-100">Gestionar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>