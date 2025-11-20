<?php
session_start();
require '../config/db.php'; 

if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }

$id_usuario = $_SESSION['user_id'];
$mensaje = "";
$tipo_mensaje = "";

// Lógica Foto
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
                $mensaje = "Foto actualizada."; $tipo_mensaje = "success";
            }
        }
    }
}

// Lógica Password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cambiar_pass'])) {
    $pass_actual = $_POST['pass_actual'];
    $pass_nueva = $_POST['password']; 
    
    $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE id = ?");
    $stmt->execute([$id_usuario]);
    $user_db = $stmt->fetch();

    $es_correcta = password_verify($pass_actual, $user_db['password']) || ($pass_actual === $user_db['password']);

    if ($es_correcta) {
        $hash_nuevo = password_hash($pass_nueva, PASSWORD_DEFAULT);
        $stmtUp = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        if ($stmtUp->execute([$hash_nuevo, $id_usuario])) {
             $mensaje = "Contraseña actualizada exitosamente."; $tipo_mensaje = "success";
        }
    } else {
        $mensaje = "La contraseña actual es incorrecta."; $tipo_mensaje = "danger";
    }
}

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
        <a href="dashboard.php" class="btn btn-secondary mb-3">&larr; Volver</a>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white text-center">
                        <h4>Mi Perfil</h4>
                    </div>
                    <div class="card-body text-center">
                        
                        <img src="<?php echo $foto_url; ?>" class="rounded-circle mb-3 border border-3" width="120" height="120" style="object-fit: cover;">
                        <h3><?php echo $usuario['nombre']; ?></h3>
                        
                        <?php if($mensaje): ?>
                            <div class="alert alert-<?php echo $tipo_mensaje; ?> mt-3"><?php echo $mensaje; ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" class="mb-4 mt-3">
                            <div class="input-group">
                                <input type="file" name="foto_perfil" class="form-control">
                                <button class="btn btn-outline-primary" type="submit">Subir Foto</button>
                            </div>
                        </form>

                        <hr>

                        <h5 class="text-start">Seguridad</h5>
                        <form method="POST">
                            <div class="mb-3 text-start">
                                <label>Contraseña Actual</label>
                                <input type="password" name="pass_actual" class="form-control" required>
                            </div>

                            <div class="mb-3 text-start password-container">
                                <label>Nueva Contraseña</label>
                                <input type="password" id="inputPass" name="password" class="form-control" placeholder="Escribe nueva clave..." required>
                                <div class="password-strength" id="strengthBar" style="width: 0%;"></div>

                                <div id="password-requirements">
                                    <strong>Requisitos de seguridad:</strong>
                                    <ul class="mt-2">
                                        <li id="req-length" class="invalid">Mínimo 8 caracteres</li>
                                        <li id="req-lower" class="invalid">Una letra minúscula</li>
                                        <li id="req-upper" class="invalid">Una letra mayúscula</li>
                                        <li id="req-number" class="invalid">Un número</li>
                                        <li id="req-special" class="invalid">Carácter especial (#, $, %)</li>
                                    </ul>
                                </div>
                            </div>

                            <button type="submit" name="cambiar_pass" class="btn btn-warning w-100">Actualizar Contraseña</button>
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