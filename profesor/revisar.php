<?php
session_start();
require '../config/db.php';

// 1. SEGURIDAD
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'profesor') {
    header("Location: login.php"); exit;
}

if (!isset($_GET['id'])) die("Falta ID de la actividad");
$actividad_id = $_GET['id'];

// 2. GUARDAR NOTA (Proceso del formulario)
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['calificar'])) {
    $entrega_id = $_POST['entrega_id'];
    $nota = $_POST['nota'];
    $comentario = $_POST['comentario'];

    $sqlUpdate = "UPDATE entregas SET nota = :nota, comentario_profesor = :com WHERE id = :id";
    $stmtUp = $pdo->prepare($sqlUpdate);
    if ($stmtUp->execute(['nota' => $nota, 'com' => $comentario, 'id' => $entrega_id])) {
        $mensaje = "¡Nota guardada correctamente!";
    }
}

// 3. OBTENER DATOS DE LA ACTIVIDAD
$sqlAct = "SELECT * FROM actividades WHERE id = :id";
$stmtAct = $pdo->prepare($sqlAct);
$stmtAct->execute(['id' => $actividad_id]);
$actividad = $stmtAct->fetch();

// 4. OBTENER LISTA DE ENTREGAS (CON NOMBRE DEL ALUMNO)
// Aquí hacemos un JOIN para saber que el alumno_id 4 se llama "Pepito"
$sqlEntregas = "SELECT e.*, u.nombre as nombre_alumno 
                FROM entregas e 
                INNER JOIN usuarios u ON e.alumno_id = u.id 
                WHERE e.actividad_id = :id 
                ORDER BY e.fecha_entrega DESC";
$stmtEnt = $pdo->prepare($sqlEntregas);
$stmtEnt->execute(['id' => $actividad_id]);
$entregas = $stmtEnt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Revisión de Tareas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Revisando: <span class="text-primary"><?php echo htmlspecialchars($actividad['titulo']); ?></span></h3>
            <a href="ver_curso.php?id=<?php echo $actividad['programacion_id']; ?>" class="btn btn-secondary">Volver al Curso</a>
        </div>

        <?php if($mensaje): ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-body">
                <?php if(count($entregas) == 0): ?>
                    <p class="text-center text-muted">Aún no hay entregas para esta tarea.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Alumno</th>
                                    <th>Fecha Entrega</th>
                                    <th>Archivo</th>
                                    <th>Calificación (1.0 - 7.0)</th>
                                    <th>Feedback</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($entregas as $ent): ?>
                                    <tr>
                                        <td><?php echo $ent['nombre_alumno']; ?></td>
                                        <td><?php echo date("d/m H:i", strtotime($ent['fecha_entrega'])); ?></td>
                                        <td>
                                            <a href="assets/uploads/<?php echo $ent['archivo_entrega']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                📥 Ver Tarea
                                            </a>
                                        </td>
                                        
                                        <form method="POST">
                                            <input type="hidden" name="entrega_id" value="<?php echo $ent['id']; ?>">
                                            <td>
                                                <input type="number" name="nota" step="0.1" min="1.0" max="7.0" class="form-control" style="width: 80px;" value="<?php echo $ent['nota']; ?>" required>
                                            </td>
                                            <td>
                                                <input type="text" name="comentario" class="form-control" placeholder="Bien hecho..." value="<?php echo $ent['comentario_profesor']; ?>">
                                            </td>
                                            <td>
                                                <button type="submit" name="calificar" class="btn btn-success btn-sm">💾 Guardar</button>
                                            </td>
                                        </form>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>