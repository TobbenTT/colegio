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
        // Verificar contraseña (Híbrido: Hash o Texto plano para legacy)
        $login_exitoso = password_verify($password, $usuario['password']) || ($password === $usuario['password']);

        if ($login_exitoso) {
            // Guardar Sesión
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol'];
            $_SESSION['foto'] = $usuario['foto'];

            // --- NUEVO: LIMPIEZA AUTOMÁTICA (El Basurero) ---
            // Borra notificaciones de más de 7 días de antigüedad
            try {
                $pdo->query("DELETE FROM notificaciones WHERE fecha < DATE_SUB(NOW(), INTERVAL 7 DAY)");
            } catch (Exception $e) {
                // Si falla la limpieza, no detenemos el login, solo seguimos.
            }
            // ------------------------------------------------

            // Redirección
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
    <title>Acceso Institucional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="login-bg">
        <div class="login-card">
            <div class="text-center mb-4">
                <img src="https://cdn-icons-png.flaticon.com/512/167/167707.png" width="80" class="mb-3">
                <h3>Bienvenido</h3>
                <p class="text-muted">Intranet Escolar</p>
            </div>
            <?php if(!empty($mensaje)): ?>
                <div class="alert alert-danger text-center p-2 small"><?php echo $mensaje; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-floating mb-3">
                    <input type="email" name="email" class="form-control" id="floatingInput" placeholder="name@example.com" required>
                    <label for="floatingInput">Correo Institucional</label>
                </div>
                <div class="form-floating mb-4">
                    <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" required>
                    <label for="floatingPassword">Contraseña</label>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">INGRESAR</button>
            </form>
        </div>
    </div>
</body>
</html>