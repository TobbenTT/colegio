<?php
session_start();
// 1. Seguridad
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'director') { header("Location: ../login.php"); exit; }
require '../includes/funciones.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Dirección</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

    <div class="sidebar">
        <div class="logo mb-4"><i class="bi bi-bank2"></i> Dirección</div>
        <a href="dashboard.php" class="active"><i class="bi bi-grid-fill"></i> <span>Menú Principal</span></a>
        <a href="resumen.php"><i class="bi bi-bar-chart-line-fill"></i> <span>Estadísticas</span></a>
        <div class="mt-5"><a href="../logout.php" class="text-danger"><i class="bi bi-box-arrow-left"></i> <span>Salir</span></a></div>
    </div>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold">Hola, Directora <?php echo explode(" ", $_SESSION['nombre'])[0]; ?></h2>
                <p class="text-muted">Seleccione una opción para comenzar.</p>
            </div>
            <?php $foto = isset($_SESSION['foto']) ? "../assets/uploads/perfiles/".$_SESSION['foto'] : "https://cdn-icons-png.flaticon.com/512/2995/2995620.png"; ?>
            <img src="<?php echo $foto; ?>" width="50" height="50" class="rounded-circle border shadow-sm" style="object-fit: cover;">
        </div>

        <div class="row">
            
            <div class="col-md-4 mb-4">
                <a href="resumen.php" class="text-decoration-none">
                    <div class="card h-100 shadow border-0 hover-effect bg-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="card-body p-5 text-center">
                            <div class="mb-3 display-4">
                                <i class="bi bi-graph-up-arrow"></i>
                            </div>
                            <h3 class="fw-bold">Resumen y Estadísticas</h3>
                            <p class="opacity-75">Ver gráficos de conducta, asistencia y matrículas.</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow border-0 hover-effect">
                    <div class="card-body p-5 text-center">
                        <div class="mb-3 display-4 text-primary">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h3 class="fw-bold text-dark">Cuerpo Docente</h3>
                        <p class="text-muted">Gestión de profesores y cargas horarias.</p>
                        <a href="profesores.php" class="btn btn-outline-primary rounded-pill px-4 mt-2">Ver Lista</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow border-0 hover-effect">
                    <div class="card-body p-5 text-center">
                        <div class="mb-3 display-4 text-warning">
                            <i class="bi bi-file-earmark-text-fill"></i>
                        </div>
                        <h3 class="fw-bold text-dark">Reportes</h3>
                        <p class="text-muted">Descargar informes de notas y asistencia.</p>
                        <a href="reportes.php" class="btn btn-outline-warning text-dark rounded-pill px-4 mt-2">Ir a Reportes</a>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>