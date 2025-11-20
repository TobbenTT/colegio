<?php
session_start();
require '../config/db.php'; // Subimos un nivel para encontrar config

// 1. Seguridad
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }

$id_usuario = $_SESSION['user_id'];
$mensaje = "";
$tipo_mensaje = ""; // success o danger

// 2. Lógica Subir Foto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['foto_perfil'])) {
    $archivo = $_FILES['foto_perfil'];
    if ($archivo['error'] == 0) {
        $ext = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])) {
            $nombre_nuevo = "perfil_" . $id_usuario . "_" . time() . "." . $ext;
            $destino = "../assets/uploads/perfiles/";
            
            if (!file_exists($destino)) mkdir($destino, 0777, true);

            if (move_uploaded_file($archivo['tmp_name'], $destino . $nombre_nuevo)) {
                $pdo->prepare("UPDATE usuarios SET foto = ? WHERE id = ?")->execute([$nombre_nuevo, $id_usuario]);
                $mensaje = "Foto actualizada.";
                $tipo_mensaje = "success";
            }
        } else {
            $mensaje = "Solo formato JPG o PNG.";
            $tipo_mensaje = "danger";
        }
    }
}

// 3. Lógica Cambio Password (CON HASH)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cambiar_pass'])) {
    $pass_actual = $_POST['pass_actual'];
    $pass_nueva = $_POST['password']; // El name del input nuevo es 'password' para coincidir con JS si quisieras
    
    $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE id = ?");
    $stmt->execute([$id_usuario]);
    $user_db = $stmt->fetch();

    // Verificar si es correcta (Soporta hash o texto plano antiguo)
    $es_correcta = password_verify($pass_actual, $user_db['password']) || ($pass_actual === $user_db['password']);

    if ($es_correcta) {
        // Encriptamos la nueva contraseña
        $hash_nuevo = password_hash($pass_nueva, PASSWORD_DEFAULT);
        
        $stmtUp = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        if ($stmtUp->execute([$hash_nuevo, $id_usuario])) {
             $mensaje = "Contraseña actualizada y encriptada correctamente.";
             $tipo_mensaje = "success";
        }
    } else {
        $mensaje = "La contraseña actual es incorrecta.";
        $tipo_mensaje = "danger";
    }
}

// Obtener datos usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id_usuario]);
$usuario = $stmt->fetch();
$foto_url = ($usuario['foto']) ? "../assets/uploads/perfiles/" . $usuario['foto'] : "https://via.placeholder.com/150";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="../assets/css/validaciones.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container mt-5 mb-5">
        <a href="dashboard.php" class="btn btn-secondary mb-3">&larr; Volver al Panel</a>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0">Mi Perfil</h4>
                    </div>
                    <div class="card-body">
                        
                        <div class="text-center mb-4">
                            <img src="<?php echo $foto_url; ?>" class="rounded-circle mb-3 border border-3 border-light shadow-sm" width="120" height="120" style="object-fit: cover;">
                            <h3><?php echo $usuario['nombre']; ?></h3>
                            <span class="badge bg-info text-dark"><?php echo ucfirst($usuario['rol']); ?></span>
                        </div>

                        <?php if($mensaje): ?>
                            <div class="alert alert-<?php echo $tipo_mensaje; ?>"><?php echo $mensaje; ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" class="mb-4 border-bottom pb-4">
                            <label class="form-label fw-bold">Actualizar Foto</label>
                            <div class="input-group">
                                <input type="file" name="foto_perfil" class="form-control" required>
                                <button class="btn btn-outline-primary" type="submit">Subir</button>
                            </div>
                        </form>

                        <h5 class="mb-3">Seguridad</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Contraseña Actual</label>
                                <input type="password" name="pass_actual" class="form-control" required>
                            </div>

                            <div class="mb-3 password-container">
                                <label class="form-label">Nueva Contraseña</label>
                                <input type="password" id="inputPass" name="password" class="form-control" placeholder="Mínimo 8 caracteres..." required>
                                
                                <div class="password-strength" id="strengthBar" style="width: 0%; background-color: #dc3545;"></div>

                                <div id="password-requirements">
                                    <p class="mb-2 fw-bold small">Requisitos de seguridad:</p>
                                    <ul>
                                        <li id="req-length" class="invalid">Mínimo 8 caracteres</li>
                                        <li id="req-lower" class="invalid">Una letra minúscula</li>
                                        <li id="req-upper" class="invalid">Una letra mayúscula</li>
                                        <li id="req-number" class="invalid">Un número</li>
                                        <li id="req-special" class="invalid">Un carácter especial (#, $, %)</li>
                                    </ul>
                                </div>
                            </div>

                            <button type="submit" name="cambiar_pass" class="btn btn-warning w-100 fw-bold">
                                🔒 Actualizar Contraseña
                            </button>
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