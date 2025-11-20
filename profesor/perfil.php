<?php
session_start();
require '../config/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pass_actual = $_POST['pass_actual'];
    $pass_nueva = $_POST['pass_nueva'];
    
    // Verificar contraseña actual
    $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    // NOTA: Como al principio las creaste texto plano, verificamos si coinciden directo.
    // Si ya estuvieran encriptadas, usaríamos password_verify($pass_actual, $user['password'])
    if ($pass_actual == $user['password']) {
        
        // AHORA SÍ ENCRIPTAMOS LA NUEVA (Seguridad Real)
        // OJO: Si haces esto, debes actualizar login.php para que sepa leer encriptadas
        // Por simplicidad para este tutorial mantendremos texto plano, 
        // PERO te dejo comentado cómo sería la seguridad real:
        
        // $hash = password_hash($pass_nueva, PASSWORD_DEFAULT); 
        // $sql = "UPDATE usuarios SET password = ? WHERE id = ?";
        // $stmt->execute([$hash, $_SESSION['user_id']]);
        
        // Versión simple (Texto plano):
        $sql = "UPDATE usuarios SET password = ? WHERE id = ?";
        $stmtUpdate = $pdo->prepare($sql);
        if ($stmtUpdate->execute([$pass_nueva, $_SESSION['user_id']])) {
             $mensaje = "Contraseña actualizada correctamente.";
        }
    } else {
        $mensaje = "La contraseña actual no es correcta.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Mi Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">Cambiar Contraseña</div>
                    <div class="card-body">
                        <?php if($mensaje): ?><div class="alert alert-info"><?php echo $mensaje; ?></div><?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label>Contraseña Actual</label>
                                <input type="password" name="pass_actual" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Nueva Contraseña</label>
                                <input type="password" name="pass_nueva" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-warning w-100">Actualizar</button>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <a href="javascript:history.back()">Volver</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>