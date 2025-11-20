<?php
session_start();
require 'config/db.php';
if ($_SESSION['rol'] != 'apoderado') { header("Location: login.php"); exit; }

$papa_id = $_SESSION['user_id'];

// BUSCAR HIJOS
$sql = "SELECT u.id, u.nombre, c.nombre as curso 
        FROM familia f
        JOIN usuarios u ON f.alumno_id = u.id
        JOIN matriculas m ON u.id = m.alumno_id
        JOIN cursos c ON m.curso_id = c.id
        WHERE f.apoderado_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$papa_id]);
$hijos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Portal Apoderado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary mb-5">
        <div class="container">
            <span class="navbar-brand">Portal Familia</span>
            <span class="text-white">Hola, <?php echo $_SESSION['nombre']; ?> | <a href="../logout.php" class="text-white fw-bold">Salir</a></span>
        </div>
    </nav>

    <div class="container">
        <h3>Seleccione Alumno</h3>
        <div class="row mt-4">
            <?php foreach($hijos as $hijo): ?>
                <div class="col-md-4">
                    <div class="card shadow text-center">
                        <div class="card-body">
                            <img src="https://cdn-icons-png.flaticon.com/512/194/194938.png" width="100" class="mb-3">
                            <h4><?php echo $hijo['nombre']; ?></h4>
                            <p class="text-muted"><?php echo $hijo['curso']; ?></p>
                            
                            <a href="ver_hijo.php?id=<?php echo $hijo['id']; ?>" class="btn btn-primary w-100">Ver Notas y Conducta</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>