<?php
session_start();
require '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'administrador') {
    header("Location: login.php"); 
    exit;
}
$mensaje = "";

// 1. PROCESAR FORMULARIO DE ASIGNACIÓN
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['asignar'])) {
    $curso = $_POST['curso_id'];
    $materia = $_POST['asignatura_id'];
    $profe = $_POST['profesor_id'];

    // Validar que no exista esa combinación
    $check = $pdo->prepare("SELECT id FROM programacion_academica WHERE curso_id=? AND asignatura_id=?");
    $check->execute([$curso, $materia]);
    
    if($check->fetch()) {
        $mensaje = "¡Error! Ese curso ya tiene profesor para esa materia.";
    } else {
        $sql = "INSERT INTO programacion_academica (curso_id, asignatura_id, profesor_id) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$curso, $materia, $profe]);
        $mensaje = "Asignación creada correctamente.";
    }
}

// 2. CARGAR LISTAS PARA LOS SELECTS
$cursos = $pdo->query("SELECT * FROM cursos")->fetchAll();
$asignaturas = $pdo->query("SELECT * FROM asignaturas")->fetchAll();
$profesores = $pdo->query("SELECT * FROM usuarios WHERE rol = 'profesor'")->fetchAll();

// 3. LISTA DE ASIGNACIONES ACTUALES
$lista = $pdo->query("SELECT pa.id, c.nombre as c, a.nombre as a, u.nombre as p 
                      FROM programacion_academica pa 
                      JOIN cursos c ON pa.curso_id = c.id
                      JOIN asignaturas a ON pa.asignatura_id = a.id
                      JOIN usuarios u ON pa.profesor_id = u.id
                      ORDER BY c.nombre")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Asignar Profesores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <a href="dashboard.php" class="btn btn-secondary mb-3">&larr; Volver</a>
        <h3>Asignar Carga Académica</h3>
        
        <?php if($mensaje): ?><div class="alert alert-info"><?php echo $mensaje; ?></div><?php endif; ?>

        <div class="card p-4 shadow-sm mb-4">
            <form method="POST" class="row g-3">
                <div class="col-md-3">
                    <label>Curso</label>
                    <select name="curso_id" class="form-select" required>
                        <option value="">Seleccionar...</option>
                        <?php foreach($cursos as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo $c['nombre']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Asignatura</label>
                    <select name="asignatura_id" class="form-select" required>
                        <option value="">Seleccionar...</option>
                        <?php foreach($asignaturas as $a): ?>
                            <option value="<?php echo $a['id']; ?>"><?php echo $a['nombre']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Profesor Encargado</label>
                    <select name="profesor_id" class="form-select" required>
                        <option value="">Seleccionar...</option>
                        <?php foreach($profesores as $p): ?>
                            <option value="<?php echo $p['id']; ?>"><?php echo $p['nombre']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" name="asignar" class="btn btn-primary w-100">Guardar</button>
                </div>
            </form>
        </div>

        <h4>Distribución Actual</h4>
        <table class="table table-modern bg-white border align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Curso</th>
                    <th>Asignatura</th>
                    <th>Profesor</th>
                    <th>Horario</th> </tr>
            </thead>
            <tbody>
                <?php foreach($lista as $l): ?>
                    <tr>
                        <td><?php echo $l['c']; ?></td>
                        <td><?php echo $l['a']; ?></td>
                        <td><?php echo $l['p']; ?></td>
                        <td>
                            <a href="horarios.php?id=<?php echo $l['id']; ?>" class="btn btn-sm btn-info text-white">
                                📅 Configurar Horas
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>