<?php
session_start();
require '../config/db.php';

// 1. SEGURIDAD
if (!isset($_SESSION['user_id']) || ($_SESSION['rol'] != 'profesor' && $_SESSION['rol'] != 'director')) {
    header("Location: login.php"); exit;
}

if (!isset($_GET['id'])) die("Falta ID del curso");
$prog_id = $_GET['id'];
$profesor_id = $_SESSION['user_id'];

// 2. INSERTAR ANOTACIÓN (POST)
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_nota'])) {
    $alumno_destinatario = $_POST['alumno_id'];
    $tipo = $_POST['tipo'];
    $detalle = $_POST['detalle'];
    
    $sqlInsert = "INSERT INTO anotaciones (alumno_id, autor_id, tipo, detalle) VALUES (:alum, :autor, :tipo, :det)";
    $stmtI = $pdo->prepare($sqlInsert);
    if ($stmtI->execute(['alum'=>$alumno_destinatario, 'autor'=>$profesor_id, 'tipo'=>$tipo, 'det'=>$detalle])) {
        $mensaje = "Anotación registrada correctamente.";
    }
}

// 3. OBTENER DATOS DEL CURSO Y ALUMNOS
// Primero obtenemos el curso_id basado en la programación actual
$sqlCurso = "SELECT pa.curso_id, c.nombre as nombre_curso 
             FROM programacion_academica pa 
             JOIN cursos c ON pa.curso_id = c.id 
             WHERE pa.id = :id";
$stmtC = $pdo->prepare($sqlCurso);
$stmtC->execute(['id' => $prog_id]);
$datos_curso = $stmtC->fetch();

if(!$datos_curso) die("Curso no encontrado");

// Ahora buscamos a los alumnos matriculados en ese curso
$sqlAlumnos = "SELECT u.id, u.nombre 
               FROM matriculas m 
               JOIN usuarios u ON m.alumno_id = u.id 
               WHERE m.curso_id = :curso_id AND u.rol = 'alumno'";
$stmtAlu = $pdo->prepare($sqlAlumnos);
$stmtAlu->execute(['curso_id' => $datos_curso['curso_id']]);
$alumnos = $stmtAlu->fetchAll();

// 4. HISTORIAL DE ANOTACIONES (Para que el profe vea lo que puso)
$sqlHistorial = "SELECT a.*, u.nombre as alumno_nombre 
                 FROM anotaciones a 
                 JOIN usuarios u ON a.alumno_id = u.id 
                 WHERE a.autor_id = :profe_id 
                 ORDER BY a.fecha DESC";
$stmtHist = $pdo->prepare($sqlHistorial);
$stmtHist->execute(['profe_id' => $profesor_id]);
$historial = $stmtHist->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Libro de Anotaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Libro de Clases: <?php echo $datos_curso['nombre_curso']; ?></h3>
            <a href="ver_curso.php?id=<?php echo $prog_id; ?>" class="btn btn-secondary">Volver al Curso</a>
        </div>

        <?php if($mensaje): ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">Nueva Anotación</div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label>Alumno:</label>
                                <select name="alumno_id" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach($alumnos as $alu): ?>
                                        <option value="<?php echo $alu['id']; ?>"><?php echo $alu['nombre']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Tipo:</label>
                                <select name="tipo" class="form-select" required>
                                    <option value="positiva">Positiva (+)</option>
                                    <option value="negativa">Negativa (-)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Detalle:</label>
                                <textarea name="detalle" class="form-control" rows="3" required placeholder="Ej: Interrumpe constantemente..."></textarea>
                            </div>
                            <button type="submit" name="crear_nota" class="btn btn-primary w-100">Guardar en Hoja de Vida</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header">Historial Registrado (Por ti)</div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Alumno</th>
                                    <th>Tipo</th>
                                    <th>Detalle</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($historial as $nota): ?>
                                    <tr>
                                        <td><?php echo date("d/m", strtotime($nota['fecha'])); ?></td>
                                        <td><?php echo $nota['alumno_nombre']; ?></td>
                                        <td>
                                            <span class="badge <?php echo ($nota['tipo']=='positiva')?'bg-success':'bg-danger'; ?>">
                                                <?php echo strtoupper($nota['tipo']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $nota['detalle']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>