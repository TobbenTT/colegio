<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'profesor') { header("Location: ../login.php"); exit; }

if (!isset($_GET['id'])) die("Falta ID.");
$prog_id = $_GET['id'];
$profesor_id = $_SESSION['user_id'];

$mensaje = "";
$tipo_msg = "";

// 1. GUARDAR ANOTACIÓN
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar_nota'])) {
    $alumno_id = $_POST['alumno_id'];
    $tipo = $_POST['tipo'];
    $detalle = $_POST['detalle'];
    
    if(!empty($alumno_id) && !empty($detalle)){
        $sql = "INSERT INTO anotaciones (alumno_id, autor_id, tipo, detalle) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$alumno_id, $profesor_id, $tipo, $detalle])) {
            $mensaje = "Anotación registrada correctamente.";
            $tipo_msg = "success";
        }
    }
}

// 2. DATOS DEL CURSO
$sqlCurso = "SELECT c.nombre as curso, a.nombre as materia, c.id as curso_id
             FROM programacion_academica pa 
             JOIN cursos c ON pa.curso_id = c.id 
             JOIN asignaturas a ON pa.asignatura_id = a.id
             WHERE pa.id = ?";
$stmtC = $pdo->prepare($sqlCurso);
$stmtC->execute([$prog_id]);
$info = $stmtC->fetch();

// 3. LISTA DE ALUMNOS (Para el Select)
$sqlAlumnos = "SELECT u.id, u.nombre FROM matriculas m 
               JOIN usuarios u ON m.alumno_id = u.id 
               WHERE m.curso_id = ? ORDER BY u.nombre";
$stmtAlu = $pdo->prepare($sqlAlumnos);
$stmtAlu->execute([$info['curso_id']]);
$alumnos = $stmtAlu->fetchAll();

// 4. HISTORIAL DE ANOTACIONES (Solo las hechas por este profe en este curso, o todas las del curso?)
// Por privacidad, generalmente el profe ve lo que él puso o lo general. Mostraremos las que puso ESTE profe.
$sqlHist = "SELECT an.*, u.nombre as alumno, u.foto 
            FROM anotaciones an
            JOIN usuarios u ON an.alumno_id = u.id
            WHERE an.autor_id = ? 
            ORDER BY an.fecha DESC";
$stmtH = $pdo->prepare($sqlHist);
$stmtH->execute([$profesor_id]);
$historial = $stmtH->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Libro de Clases</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

    <div class="sidebar">
        <div class="logo mb-4"><i class="bi bi-mortarboard-fill"></i> ColegioApp</div>
        <a href="dashboard.php" class="active"><i class="bi bi-speedometer2"></i> <span>Mis Cursos</span></a>
        <a href="mensajes.php"><i class="bi bi-chat-dots"></i> <span>Mensajería</span></a>
        <a href="perfil.php"><i class="bi bi-person-circle"></i> <span>Mi Perfil</span></a>
        <div class="mt-5"><a href="../logout.php" class="text-danger"><i class="bi bi-box-arrow-left"></i> <span>Salir</span></a></div>
    </div>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Hoja de Vida: <?php echo $info['curso']; ?></h4>
                <p class="text-muted mb-0 small">Registro de observaciones para <?php echo $info['materia']; ?></p>
            </div>
            <a href="ver_curso.php?id=<?php echo $prog_id; ?>" class="btn btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-left"></i> Volver al Curso
            </a>
        </div>

        <?php if($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_msg; ?> shadow-sm border-0 mb-4"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <div class="row">
            
            <div class="col-lg-4 mb-4">
                <div class="card shadow border-0 sticky-top" style="top: 20px; z-index: 1;">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-pen-fill"></i> Nueva Anotación</h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary small">Alumno</label>
                                <select name="alumno_id" class="form-select" required>
                                    <option value="">Seleccionar...</option>
                                    <?php foreach($alumnos as $alu): ?>
                                        <option value="<?php echo $alu['id']; ?>"><?php echo $alu['nombre']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary small">Tipo de Observación</label>
                                <select name="tipo" class="form-select" required>
                                    <option value="positiva">🟢 Positiva / Mérito</option>
                                    <option value="negativa">🔴 Negativa / Falta</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary small">Detalle</label>
                                <textarea name="detalle" class="form-control" rows="4" placeholder="Describe la situación..." required></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="guardar_nota" class="btn btn-primary fw-bold">REGISTRAR</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-secondary">Historial Reciente</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if(count($historial) == 0): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-journal-check display-1 opacity-25"></i>
                                <p class="mt-2">No has registrado anotaciones aún.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4">Fecha</th>
                                            <th>Alumno</th>
                                            <th>Tipo</th>
                                            <th>Detalle</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($historial as $nota): ?>
                                            <tr>
                                                <td class="ps-4 text-muted small" style="width: 120px;">
                                                    <?php echo date("d/m/Y", strtotime($nota['fecha'])); ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php $foto = $nota['foto'] ? "../assets/uploads/perfiles/".$nota['foto'] : "https://cdn-icons-png.flaticon.com/512/2995/2995620.png"; ?>
                                                        <img src="<?php echo $foto; ?>" width="30" height="30" class="rounded-circle me-2">
                                                        <span class="fw-bold text-dark"><?php echo $nota['alumno']; ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if($nota['tipo']=='positiva'): ?>
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Positiva</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">Negativa</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="small text-muted"><?php echo $nota['detalle']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>