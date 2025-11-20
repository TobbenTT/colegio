<?php
session_start();
require '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'administrador') {
    header("Location: login.php"); 
    exit;
}
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $pass_raw = $_POST['password']; // La contraseña escrita (ej: 12345)
    $rol = $_POST['rol'];

    // ENCRIPTAR ANTES DE GUARDAR
    $pass_hash = password_hash($pass_raw, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (:n, :e, :p, :r)";
    $stmt = $pdo->prepare($sql);
    
    try {
        // Guardamos $pass_hash en lugar de $pass_raw
        $stmt->execute(['n'=>$nombre, 'e'=>$email, 'p'=>$pass_hash, 'r'=>$rol]);
        $mensaje = "Usuario creado correctamente (Clave encriptada).";
    } catch (PDOException $e) {
        $mensaje = "Error: " . $e->getMessage();
    }
}

// Listar últimos 10 usuarios
$usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY id DESC LIMIT 10")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Gestión Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <a href="dashboard.php" class="btn btn-secondary mb-3">&larr; Volver</a>
        <h3>Crear Nuevo Usuario</h3>
        <?php if($mensaje): ?><div class="alert alert-info"><?php echo $mensaje; ?></div><?php endif; ?>

        <div class="card p-4 mb-4">
            <form method="POST">
                <div class="row">
                    <div class="col-md-3"><input type="text" name="nombre" class="form-control" placeholder="Nombre Completo" required></div>
                    <div class="col-md-3"><input type="email" name="email" class="form-control" placeholder="Correo" required></div>
                    <div class="col-md-3"><input type="text" name="password" class="form-control" placeholder="Contraseña" required></div>
                    <div class="col-md-2">
                        <select name="rol" class="form-select">
                            <option value="alumno">Alumno</option>
                            <option value="profesor">Profesor</option>
                            <option value="director">Director</option>
                        </select>
                    </div>
                    <div class="col-md-1"><button class="btn btn-success w-100">+</button></div>
                </div>
            </form>
        </div>

        <h4>Usuarios Recientes</h4>
        <table class="table bg-white">
            <tr><th>Nombre</th><th>Email</th><th>Rol</th></tr>
            <?php foreach($usuarios as $u): ?>
                <tr><td><?php echo $u['nombre']; ?></td><td><?php echo $u['email']; ?></td><td><?php echo $u['rol']; ?></td></tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>