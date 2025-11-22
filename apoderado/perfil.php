<?php
session_start();
require '../config/db.php';
require '../includes/funciones.php'; // <--- IMPORTANTE

if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }

$id_usuario = $_SESSION['user_id'];
$mensaje = "";
$tipo_msg = "";

// ... (Todo tu código de subir foto y contraseña se mantiene igual) ...
// ... (Solo asegúrate de calcular las notificaciones abajo) ...

$notificaciones_pendientes = contarNotificacionesNoLeidas($pdo, $_SESSION['user_id']);

// ... (Aquí sigue tu lógica de obtener usuario) ...
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/validaciones.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        /* Estilos de la foto de perfil */
        .profile-header-bg {
            height: 150px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 15px 15px 0 0;
            position: relative;
        }
        .profile-avatar-wrapper {
            position: absolute;
            bottom: -50px;
            left: 50%;
            transform: translateX(-50%);
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid white;
            object-fit: cover;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            background: white;
        }
        .camera-icon {
            position: absolute;
            bottom: 0;
            right: 0;
            background: var(--primary-color);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 3px solid white;
            transition: 0.2s;
        }
        .camera-icon:hover { transform: scale(1.1); background: var(--accent-color); }
    </style>
</head>
<body>

    <?php include '../includes/sidebar_apoderado.php'; ?>

    <div class="main-content">
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/validaciones.js"></script>
</body>
</html>