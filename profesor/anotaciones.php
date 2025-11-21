<?php
session_start();
require '../config/db.php';
require_once '../includes/funciones.php'; // IMPORTANTE

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'profesor') { header("Location: ../login.php"); exit; }

if (!isset($_GET['id'])) die("Falta ID.");
$prog_id = $_GET['id'];
$profesor_id = $_SESSION['user_id'];

$mensaje = "";

// 1. GUARDAR ANOTACIÓN
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar_nota'])) {
    $alumno_id = $_POST['alumno_id'];
    $tipo = $_POST['tipo'];
    $detalle = $_POST['detalle'];
    
    if(!empty($alumno_id) && !empty($detalle)){
        $sql = "INSERT INTO anotaciones (alumno_id, autor_id, tipo, detalle) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$alumno_id, $profesor_id, $tipo, $detalle])) {
            
            // --- NOTIFICAR AL ALUMNO ---
            $tipo_texto = ($tipo == 'positiva') ? "Felicitación" : "Observación";
            $msg = "Tienes una nueva $tipo_texto en tu Hoja de Vida.";
            enviarNotificacion($pdo, $alumno_id, $msg, 'mis_anotaciones.php');
            // ---------------------------

            $mensaje = "Anotación registrada y notificación enviada.";
        }
    }
}

// 2. DATOS
$sqlCurso = "SELECT c.nombre as curso, a.nombre as materia, c.id as curso_id FROM programacion_academica pa JOIN cursos c ON pa.curso_id = c.id JOIN asignaturas a ON pa.asignatura_id = a.id WHERE pa.id = ?";
$info = $pdo->prepare($sqlCurso); $info->execute([$prog_id]); $info = $info->fetch();

$sqlAlumnos = "SELECT u.id, u.nombre FROM matriculas m JOIN usuarios u ON m.alumno_id = u.id WHERE m.curso_id = ? ORDER BY u.nombre";
$alumnos = $pdo->prepare($sqlAlumnos); $alumnos->execute([$info['curso_id']]); $alumnos = $alumnos->fetchAll();

// 3. OBTENER HISTORIAL (CON FILTRO)
$filtro_alumno_id = $_GET['filtro_alumno_id'] ?? '';

$sqlHist = "SELECT an.*, u.nombre as alumno, u.foto 
            FROM anotaciones an 
            JOIN usuarios u ON an.alumno_id = u.id 
            JOIN matriculas m ON u.id = m.alumno_id
            WHERE an.autor_id = :profesor_id AND m.curso_id = :curso_id";

$params = ['profesor_id' => $profesor_id, 'curso_id' => $info['curso_id']];

if (!empty($filtro_alumno_id)) {
    $sqlHist .= " AND an.alumno_id = :alumno_id";
    $params['alumno_id'] = $filtro_alumno_id;
}
$sqlHist .= " ORDER BY an.fecha DESC";
$historial = $pdo->prepare($sqlHist); $historial->execute($params); $historial = $historial->fetchAll();
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

    <?php include '../includes/sidebar_profesor.php'; ?>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Hoja de Vida: <?php echo $info['curso']; ?></h4>
                <p class="text-muted mb-0 small">Registro de observaciones.</p>
            </div>
            <a href="ver_curso.php?id=<?php echo $prog_id; ?>" class="btn btn-outline-secondary rounded-pill">Volver al Curso</a>
        </div>

        <?php if($mensaje): ?>
            <div class="alert alert-success shadow-sm border-0 mb-4"><?php echo $mensaje; ?></div>
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
                                <label class="form-label fw-bold text-secondary small">Tipo</label>
                                <select name="tipo" class="form-select" required>
                                    <option value="positiva">🟢 Positiva</option>
                                    <option value="negativa">🔴 Negativa</option>
                                </select>
                            </div>
                            <div class="mb-3">
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
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body bg-light rounded-3">
                        <form method="GET" class="d-flex align-items-center gap-3">
                            <input type="hidden" name="id" value="<?php echo $prog_id; ?>">
                            <label class="fw-bold text-secondary"><i class="bi bi-funnel"></i> Filtrar por Alumno:</label>
                            <select name="filtro_alumno_id" class="form-select" onchange="this.form.submit()">
                                <option value="">Ver todos los del curso</option>
                                <?php foreach($alumnos as $alu): ?>
                                    <option value="<?php echo $alu['id']; ?>" <?php echo ($filtro_alumno_id == $alu['id']) ? 'selected' : ''; ?>>
                                        <?php echo $alu['nombre']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if($filtro_alumno_id): ?>
                                <a href="anotaciones.php?id=<?php echo $prog_id; ?>" class="btn btn-outline-danger">Limpiar</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <?php if(count($historial) == 0): ?>
                            <div class="text-center py-5 text-muted">Sin registros.</div>
                        <?php else: ?>
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light"><tr><th class="ps-4">Fecha</th><th>Alumno</th><th>Tipo</th><th>Detalle</th></tr></thead>
                                <tbody>
                                    <?php foreach($historial as $nota): ?>
                                        <tr>
                                            <td class="ps-4 small"><?php echo date("d/m", strtotime($nota['fecha'])); ?></td>
                                            <td class="fw-bold"><?php echo $nota['alumno']; ?></td>
                                            <td>
                                                <span class="badge <?php echo ($nota['tipo']=='positiva')?'bg-success':'bg-danger'; ?> rounded-pill">
                                                    <?php echo ucfirst($nota['tipo']); ?>
                                                </span>
                                            </td>
                                            <td class="small text-muted"><?php echo $nota['detalle']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>