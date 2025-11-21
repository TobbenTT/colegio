<?php
session_start();
// 1. Seguridad
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'director') { header("Location: ../login.php"); exit; }
require '../includes/funciones.php';

// PUBLICAR ANUNCIO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['publicar_anuncio'])) {
    $titulo = $_POST['titulo'];
    $mensaje = $_POST['mensaje'];
    $tipo = $_POST['tipo'];
    
    $pdo->prepare("INSERT INTO anuncios (titulo, mensaje, autor_id, tipo) VALUES (?, ?, ?, ?)")
        ->execute([$titulo, $mensaje, $_SESSION['user_id'], $tipo]);
        
    // Opcional: Notificar a TODOS los usuarios del sistema (sería masivo, cuidado)
}
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
<?php include '../includes/sidebar_director.php'; ?>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold">Hola, Directora <?php echo explode(" ", $_SESSION['nombre'])[0]; ?></h2>
                <p class="text-muted">Seleccione una opción para comenzar.</p>
            </div>
            <?php $foto = isset($_SESSION['foto']) ? "../assets/uploads/perfiles/".$_SESSION['foto'] : "https://cdn-icons-png.flaticon.com/512/2995/2995620.png"; ?>
            <img src="<?php echo $foto; ?>" width="50" height="50" class="rounded-circle border shadow-sm" style="object-fit: cover;">
        </div>
        <?php include '../includes/widget_anuncios.php'; ?>

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
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-megaphone-fill"></i> Publicar Anuncio Oficial</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-8 mb-2">
                                <input type="text" name="titulo" class="form-control" placeholder="Título (Ej: Suspensión de clases)" required>
                            </div>
                            <div class="col-md-4 mb-2">
                                <select name="tipo" class="form-select">
                                    <option value="informativo">ℹ️ Info</option>
                                    <option value="urgente">🚨 Urgente</option>
                                </select>
                            </div>
                        </div>
                        <textarea name="mensaje" class="form-control mb-3" rows="2" placeholder="Escribe el comunicado..." required></textarea>
                        <button type="submit" name="publicar_anuncio" class="btn btn-primary w-100 fw-bold">PUBLICAR EN CARTELERA</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>