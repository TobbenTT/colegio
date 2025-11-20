<?php
require '../config/db.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'director') {
    header("Location: login.php"); exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-danger mb-5">
        <div class="container">
            <span class="navbar-brand">Administración Escolar</span>
            <span class="text-white">Hola, <?php echo $_SESSION['nombre']; ?> | <a href="../logout.php" class="text-white fw-bold">Salir</a></span>
        </div>
    </nav>

    <div class="container">
        <h2 class="text-center mb-5">¿Qué deseas gestionar hoy?</h2>
        
        <div class="row text-center">
            <div class="col-md-3 mb-4">
                <div class="card shadow h-100 hover-effect">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <h1 class="text-primary"><i class="bi bi-bar-chart-line"></i></h1>
                        <h5 class="card-title">Resumen y Estadísticas</h5>
                        <p class="card-text text-muted">Ver gráficos de conducta y contadores.</p>
                        <a href="resumen.php" class="btn btn-outline-primary mt-auto">Ver Resumen</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>