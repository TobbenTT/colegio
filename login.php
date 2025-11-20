<?php
session_start();
require 'config/db.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        // LÓGICA HÍBRIDA DE SEGURIDAD
        // 1. Verificamos si es un Hash seguro
        $login_exitoso = password_verify($password, $usuario['password']);

        // 2. Si falló, verificamos si es una clave antigua (texto plano)
        if (!$login_exitoso && $password === $usuario['password']) {
            $login_exitoso = true;
            
            // Opcional: Actualizar automáticamente a encriptada para la próxima
            $nuevo_hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?")->execute([$nuevo_hash, $usuario['id']]);
        }

        if ($login_exitoso) {
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol'];

            // Redirección según rol
            switch ($usuario['rol']) {
                case 'alumno': header("Location: alumno/dashboard.php"); break;
                case 'profesor': header("Location: profesor/dashboard.php"); break;
                case 'director': header("Location: director/dashboard.php"); break;
                case 'administrador': header("Location: admin/dashboard.php"); break;
                case 'apoderado': header("Location: apoderado/dashboard.php"); break;
            }
            exit;
        } else {
            $mensaje = "Contraseña incorrecta.";
        }
    } else {
        $mensaje = "Usuario no encontrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Institucional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet"> </head>
<body>
    
    <div class="login-bg">
        <div class="login-card">
            <div class="text-center mb-4">
                <img src="https://cdn-icons-png.flaticon.com/512/167/167707.png" width="80" class="mb-3">
                <h3>Bienvenido</h3>
                <p class="text-muted">Sistema de Gestión Escolar</p>
            </div>

            <?php if(!empty($mensaje)): ?>
                <div class="alert alert-danger text-center p-2 small"><?php echo $mensaje; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-floating mb-3">
                    <input type="email" name="email" class="form-control" id="floatingInput" placeholder="name@example.com" required>
                    <label for="floatingInput">Correo Electrónico</label>
                </div>
                <div class="form-floating mb-4">
                    <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" required>
                    <label for="floatingPassword">Contraseña</label>
                </div>
                <button type="submit" class="btn btn-success w-100 py-2 fw-bold">INICIAR SESIÓN</button>
            </form>
            
            <div class="text-center mt-3">
                <small class="text-muted">¿Olvidaste tu contraseña? Contacta a administración.</small>
            </div>
        </div>
    </div>

</body>
</html>