<?php
session_start();
require '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'administrador') {
    header("Location: login.php"); 
    exit;
}
// Lógica para crear Curso
if (isset($_POST['crear_curso'])) {
    $stmt = $pdo->prepare("INSERT INTO cursos (nombre) VALUES (?)");
    $stmt->execute([$_POST['nombre_curso']]);
}

// Lógica para crear Asignatura
if (isset($_POST['crear_asignatura'])) {
    $stmt = $pdo->prepare("INSERT INTO asignaturas (nombre) VALUES (?)");
    $stmt->execute([$_POST['nombre_asignatura']]);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Cursos y Materias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <a href="dashboard.php" class="btn btn-secondary mb-3">&larr; Volver</a>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card p-3">
                    <h5>Nuevo Curso</h5>
                    <form method="POST" class="d-flex gap-2">
                        <input type="text" name="nombre_curso" class="form-control" placeholder="Ej: 5°B">
                        <button type="submit" name="crear_curso" class="btn btn-primary">Crear</button>
                    </form>
                    <hr>
                    <h6>Existentes:</h6>
                    <ul>
                        <?php foreach($pdo->query("SELECT * FROM cursos") as $c) echo "<li>{$c['nombre']}</li>"; ?>
                    </ul>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card p-3">
                    <h5>Nueva Asignatura</h5>
                    <form method="POST" class="d-flex gap-2">
                        <input type="text" name="nombre_asignatura" class="form-control" placeholder="Ej: Química">
                        <button type="submit" name="crear_asignatura" class="btn btn-success">Crear</button>
                    </form>
                    <hr>
                    <h6>Existentes:</h6>
                    <ul>
                        <?php foreach($pdo->query("SELECT * FROM asignaturas") as $a) echo "<li>{$a['nombre']}</li>"; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>