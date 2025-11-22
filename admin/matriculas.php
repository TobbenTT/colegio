<?php
session_start();
require '../config/db.php';
require '../includes/funciones.php';
// 1. Seguridad
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'administrador') { header("Location: ../login.php"); exit; }

$mensaje = "";
$tipo_msg = "";
$datos_editar = null;

// 2. MODO EDICIÓN: CARGAR DATOS
if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    $stmtEdit = $pdo->prepare("SELECT * FROM matriculas WHERE id = ?");
    $stmtEdit->execute([$id_editar]);
    $datos_editar = $stmtEdit->fetch();
}

// 3. GUARDAR CAMBIOS (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar_matricula'])) {
    $alumno_id = $_POST['alumno_id'];
    $curso_id = $_POST['curso_id'];
    $anio = $_POST['anio'];
    $id_update = $_POST['id_matricula'] ?? ''; // Campo oculto

    // Validar si ya existe esa combinación (Alumno + Año) PERO excluyendo el registro actual si estamos editando
    $sqlCheck = "SELECT id FROM matriculas WHERE alumno_id = ? AND anio = ?";
    $paramsCheck = [$alumno_id, $anio];
    
    if (!empty($id_update)) {
        $sqlCheck .= " AND id != ?"; // Si editamos, ignoramos nuestro propio ID
        $paramsCheck[] = $id_update;
    }

    $check = $pdo->prepare($sqlCheck);
    $check->execute($paramsCheck);
    
    if ($check->fetch()) {
        $mensaje = "El alumno ya tiene matrícula en ese año."; 
        $tipo_msg = "warning";
    } else {
        if (!empty($id_update)) {
            // --- UPDATE ---
            $sql = "UPDATE matriculas SET alumno_id=?, curso_id=?, anio=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$alumno_id, $curso_id, $anio, $id_update])) {
                $mensaje = "Matrícula actualizada (Cambio de curso realizado).";
                $tipo_msg = "info";
                $datos_editar = null; // Limpiar formulario
            }
        } else {
            // --- INSERT ---
            $sql = "INSERT INTO matriculas (alumno_id, curso_id, anio) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$alumno_id, $curso_id, $anio])) {
                $mensaje = "Matrícula creada correctamente.";
                $tipo_msg = "success";
            }
        }
    }
}

// 4. BORRAR
if (isset($_GET['borrar'])) {
    $pdo->prepare("DELETE FROM matriculas WHERE id = ?")->execute([$_GET['borrar']]);
    header("Location: matriculas.php"); exit;
}

// 5. DATOS PARA LISTAS
// Alumnos (Traemos todos para poder editar, aunque idealmente se filtran los no matriculados en INSERT)
$alumnos = $pdo->query("SELECT id, nombre FROM usuarios WHERE rol = 'alumno' ORDER BY nombre")->fetchAll();
$cursos = $pdo->query("SELECT * FROM cursos ORDER BY nombre")->fetchAll();

// Tabla Principal
$matriculados = $pdo->query("SELECT m.id, u.nombre as alumno, c.nombre as curso, m.anio 
                             FROM matriculas m 
                             JOIN usuarios u ON m.alumno_id = u.id 
                             JOIN cursos c ON m.curso_id = c.id 
                             ORDER BY c.nombre, u.nombre")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Gestión de Matrículas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

<?php include '../includes/sidebar_admin.php'; ?>


    <div class="main-content">
        
        <h3 class="fw-bold mb-4"><i class="bi bi-mortarboard"></i> Gestión de Matrículas</h3>

        <?php if($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_msg; ?> shadow-sm border-0 mb-4 d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-2"></i> <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            
            <div class="col-lg-4 mb-4">
                <div class="card shadow border-0 sticky-top" style="top: 20px; z-index: 1;">
                    <div class="card-header <?php echo $datos_editar ? 'bg-warning text-dark' : 'bg-success text-white'; ?> py-3">
                        <h5 class="mb-0 fw-bold">
                            <?php echo $datos_editar ? '<i class="bi bi-pencil-square"></i> Editar Matrícula' : '<i class="bi bi-plus-circle"></i> Nueva Matrícula'; ?>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="matriculas.php">
                            <input type="hidden" name="id_matricula" value="<?php echo $datos_editar['id'] ?? ''; ?>">

                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary small">Alumno</label>
                                <select name="alumno_id" class="form-select" required>
                                    <option value="">Buscar estudiante...</option>
                                    <?php foreach($alumnos as $alu): 
                                        $sel = ($datos_editar && $datos_editar['alumno_id'] == $alu['id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $alu['id']; ?>" <?php echo $sel; ?>><?php echo $alu['nombre']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary small">Curso Asignado</label>
                                <select name="curso_id" class="form-select" required>
                                    <option value="">Seleccionar curso...</option>
                                    <?php foreach($cursos as $c): 
                                        $sel = ($datos_editar && $datos_editar['curso_id'] == $c['id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $c['id']; ?>" <?php echo $sel; ?>><?php echo $c['nombre']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-secondary small">Año Académico</label>
                                <input type="number" name="anio" class="form-control" 
                                       value="<?php echo $datos_editar ? $datos_editar['anio'] : date('Y'); ?>" required>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="guardar_matricula" class="btn <?php echo $datos_editar ? 'btn-warning' : 'btn-success'; ?> fw-bold py-2">
                                    <?php echo $datos_editar ? 'GUARDAR CAMBIOS' : 'CONFIRMAR MATRÍCULA'; ?>
                                </button>
                                
                                <?php if($datos_editar): ?>
                                    <a href="matriculas.php" class="btn btn-outline-secondary">Cancelar</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-secondary">Alumnos Matriculados</h5>
                        <span class="badge bg-light text-dark border"><?php echo count($matriculados); ?> Total</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-modern table-hover mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Alumno</th>
                                        <th>Curso</th>
                                        <th>Año</th>
                                        <th class="text-end pe-4">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($matriculados as $m): ?>
                                        <tr class="<?php echo ($datos_editar && $datos_editar['id'] == $m['id']) ? 'table-active' : ''; ?>">
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold me-3" style="width: 35px; height: 35px;">
                                                        <?php echo strtoupper(substr($m['alumno'], 0, 1)); ?>
                                                    </div>
                                                    <span class="fw-bold text-dark"><?php echo $m['alumno']; ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning text-dark rounded-pill px-3">
                                                    <?php echo $m['curso']; ?>
                                                </span>
                                            </td>
                                            <td class="text-muted small"><?php echo $m['anio']; ?></td>
                                            <td class="text-end pe-4">
                                                
                                                <a href="matriculas.php?editar=<?php echo $m['id']; ?>" class="btn btn-sm btn-light text-primary border me-1" title="Cambiar de curso">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>

                                                <a href="matriculas.php?borrar=<?php echo $m['id']; ?>" 
                                                   class="btn btn-sm btn-light text-danger border" 
                                                   onclick="return confirm('¿Estás seguro? Esto eliminará al alumno del curso.')"
                                                   title="Eliminar matrícula">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>