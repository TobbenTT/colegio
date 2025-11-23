<?php
session_start();

// 1. LÓGICA INTELIGENTE: SI YA ESTÁ LOGUEADO, REDIRIGIR AL DASHBOARD
if (isset($_SESSION['user_id']) && isset($_SESSION['rol'])) {
    switch ($_SESSION['rol']) {
        case 'alumno': header("Location: alumno/dashboard.php"); break;
        case 'profesor': header("Location: profesor/dashboard.php"); break;
        case 'director': header("Location: director/dashboard.php"); break;
        case 'administrador': header("Location: admin/dashboard.php"); break;
        case 'apoderado': header("Location: apoderado/dashboard.php"); break;
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido - Colegio Institucional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
    
    <style>
        /* Estilos específicos para la Landing Page */
        .hero-section {
            background: linear-gradient(rgba(44, 62, 80, 0.8), rgba(44, 62, 80, 0.8)), url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--accent-color);
            margin-bottom: 1rem;
        }
        
        .hero-btn {
            padding: 15px 40px;
            font-size: 1.2rem;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: transform 0.3s;
        }
        
        .hero-btn:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background: rgba(0,0,0,0.3); backdrop-filter: blur(5px);">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-backpack2-fill"></i> COLEGIO APP
            </a>
            <div class="d-flex">
                <a href="login.php" class="btn btn-outline-light rounded-pill px-4 fw-bold">
                    <i class="bi bi-box-arrow-in-right"></i> INTRANET
                </a>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container">
            <span class="badge bg-warning text-dark mb-3 px-3 py-2">Plataforma Educativa 2.0</span>
            <h1 class="display-3 fw-bold mb-4">Excelencia Académica Digital</h1>
            <p class="lead mb-5 opacity-75" style="max-width: 700px; margin: 0 auto;">
                Gestiona tus clases, revisa tus calificaciones y mantente conectado con la comunidad educativa desde cualquier lugar.
            </p>
            
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="login.php" class="btn btn-success hero-btn fw-bold shadow-lg">
                    Acceder al Aula Virtual
                </a>
                <a href="#" class="btn btn-outline-light hero-btn fw-bold">
                    Más Información
                </a>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container">
            <div class="row text-center g-4">
                <div class="col-md-4">
                    <div class="p-4">
                        <div class="feature-icon"><i class="bi bi-laptop"></i></div>
                        <h4>Gestión 100% Online</h4>
                        <p class="text-muted">Acceso a material de estudio, tareas y evaluaciones desde tu computador o celular.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100 transform-hover">
                        <div class="card-body p-4">
                            <div class="feature-icon"><i class="bi bi-bell-fill"></i></div>
                            <h4>Notificaciones Reales</h4>
                            <p class="text-muted">Entérate al instante de tus notas, avisos importantes y comunicados del colegio.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4">
                        <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                        <h4>Seguridad Total</h4>
                        <p class="text-muted">Tus datos académicos y personales están protegidos con los más altos estándares.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white py-4 text-center">
        <div class="container">
            <small class="opacity-50">&copy; <?php echo date('Y'); ?> Colegio Institucional. Desarrollado con tecnología moderna.</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>