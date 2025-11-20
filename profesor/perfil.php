<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }

$id_usuario = $_SESSION['user_id'];
$mensaje = "";
$tipo_msg = "";

// 1. SUBIR FOTO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['foto_perfil'])) {
    $archivo = $_FILES['foto_perfil'];
    if ($archivo['error'] == 0) {
        $ext = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])) {
            $nombre_nuevo = "perfil_" . $id_usuario . "_" . time() . "." . $ext;
            $destino = "../assets/uploads/perfiles/";
            
            if (!file_exists($destino)) mkdir($destino, 0777, true);

            if (move_uploaded_file($archivo['tmp_name'], $destino . $nombre_nuevo)) {
                // Actualizar BD
                $pdo->prepare("UPDATE usuarios SET foto = ? WHERE id = ?")->execute([$nombre_nuevo, $id_usuario]);
                
                // Actualizar SESIÓN al instante (Para que se vea en el dashboard sin re-loguear)
                $_SESSION['foto'] = $nombre_nuevo;

                $mensaje = "Foto actualizada correctamente.";
                $tipo_msg = "success";
            }
        } else {
            $mensaje = "Formato no válido. Solo JPG o PNG.";
            $tipo_msg = "danger";
        }
    }
}

// 2. CAMBIAR CONTRASEÑA
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cambiar_pass'])) {
    $pass_actual = $_POST['pass_actual'];
    $pass_nueva = $_POST['password']; 
    
    $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE id = ?");
    $stmt->execute([$id_usuario]);
    $user_db = $stmt->fetch();

    $es_correcta = password_verify($pass_actual, $user_db['password']) || ($pass_actual === $user_db['password']);

    if ($es_correcta) {
        $hash_nuevo = password_hash($pass_nueva, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?")->execute([$hash_nuevo, $id_usuario]);
        $mensaje = "Contraseña blindada y actualizada."; $tipo_msg = "success";
    } else {
        $mensaje = "La contraseña actual no coincide."; $tipo_msg = "danger";
    }
}

// Obtener datos frescos
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id_usuario]);
$usuario = $stmt->fetch();

// Lógica de visualización de foto
$foto_db = $usuario['foto'];
if ($foto_db && file_exists("../assets/uploads/perfiles/" . $foto_db)) {
    $foto_url = "../assets/uploads/perfiles/" . $foto_db;
} else {
    // Avatar por defecto si no hay foto
    $foto_url = "https://cdn-icons-png.flaticon.com/512/2995/2995620.png"; 
}
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
    <link href="../assets/css/PerfilA.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container mt-4 mb-5">
        <a href="dashboard.php" class="btn btn-outline-secondary mb-4 px-4 rounded-pill fw-bold">
            <i class="bi bi-arrow-left"></i> Volver al Panel
        </a>

        <?php if($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_msg; ?> shadow-sm border-0 mb-4 text-center fw-bold">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            
            <div class="col-md-4 mb-4">
                <div class="card shadow border-0">
                    <div class="profile-header-bg">
                        <div class="profile-avatar-wrapper">
                            <img src="<?php echo $foto_url; ?>" class="profile-avatar" id="previewImg">
                            
                            <label for="uploadPhoto" class="camera-icon">
                                <i class="bi bi-camera-fill"></i>
                            </label>
                        </div>
                    </div>

                    <div class="card-body text-center pt-5 mt-3">
                        <h3 class="fw-bold text-dark mb-0"><?php echo $usuario['nombre']; ?></h3>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 mt-2 text-uppercase">
                            <?php echo $usuario['rol']; ?>
                        </span>
                        
                        <p class="text-muted mt-3 small px-3">
                            <i class="bi bi-envelope-at"></i> <?php echo $usuario['email']; ?>
                        </p>

                        <form method="POST" enctype="multipart/form-data" id="formFoto">
                            <input type="file" name="foto_perfil" id="uploadPhoto" class="d-none" onchange="document.getElementById('formFoto').submit()">
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-secondary"><i class="bi bi-shield-lock"></i> Seguridad de la Cuenta</h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <div class="alert alert-light border-start border-4 border-warning text-muted small">
                            <i class="bi bi-info-circle-fill text-warning"></i> 
                            Te recomendamos usar una contraseña segura con mayúsculas, números y símbolos.
                        </div>

                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label fw-bold text-secondary">Contraseña Actual</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                                    <input type="password" name="pass_actual" class="form-control" placeholder="Ingresa tu clave actual" required>
                                </div>
                            </div>

                            <div class="mb-4 password-container">
                                <label class="form-label fw-bold text-secondary">Nueva Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                    <input type="password" id="inputPass" name="password" class="form-control" placeholder="Crea una nueva clave" required>
                                </div>
                                <div class="password-strength" id="strengthBar" style="width: 0%;"></div>

                                <div id="password-requirements">
                                    <strong>Requisitos:</strong>
                                    <ul class="mt-2">
                                        <li id="req-length" class="invalid">Mínimo 8 caracteres</li>
                                        <li id="req-lower" class="invalid">Una minúscula</li>
                                        <li id="req-upper" class="invalid">Una mayúscula</li>
                                        <li id="req-number" class="invalid">Un número</li>
                                        <li id="req-special" class="invalid">Símbolo (# $ %)</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" name="cambiar_pass" class="btn btn-primary px-4 fw-bold">
                                    <i class="bi bi-save"></i> Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/validaciones.js"></script>
</body>
</html>