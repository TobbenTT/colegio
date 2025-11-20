<?php
session_start();
require '../config/db.php';

// 1. Seguridad Admin
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'administrador') {
    header("Location: ../login.php"); exit;
}

if (!isset($_GET['id'])) { die("Falta ID."); }
$prog_id = $_GET['id'];
$mensaje = "";
$tipo_msg = "";
$datos_editar = null;

// --- LÓGICA DE EDICIÓN (Cargar datos) ---
if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    $stmtEdit = $pdo->prepare("SELECT * FROM horarios WHERE id = ?");
    $stmtEdit->execute([$id_editar]);
    $datos_editar = $stmtEdit->fetch();
}

// --- LÓGICA DE GUARDADO (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dia = $_POST['dia'];
    $inicio = $_POST['inicio'];
    $fin = $_POST['fin'];
    $aula = $_POST['aula'];
    $id_actualizar = $_POST['id_actualizar'];

    try {
        if (!empty($id_actualizar)) {
            // UPDATE
            $sql = "UPDATE horarios SET dia=?, hora_inicio=?, hora_fin=?, aula=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$dia, $inicio, $fin, $aula, $id_actualizar])) {
                $mensaje = "Bloque actualizado correctamente."; $tipo_msg = "info";
                $datos_editar = null; // Salir modo edición
            }
        } else {
            // INSERT
            $sql = "INSERT INTO horarios (programacion_id, dia, hora_inicio, hora_fin, aula) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$prog_id, $dia, $inicio, $fin, $aula])) {
                $mensaje = "Bloque agregado al horario."; $tipo_msg = "success";
            }
        }
    } catch (Exception $e) {
        $mensaje = "Error al guardar."; $tipo_msg = "danger";
    }
}

// --- LÓGICA DE BORRADO ---
if (isset($_GET['borrar'])) {
    $id_horario = $_GET['borrar'];
    $pdo->prepare("DELETE FROM horarios WHERE id = ?")->execute([$id_horario]);
    header("Location: horarios.php?id=" . $prog_id); exit;
}

// OBTENER DATOS GENERALES
$info = $pdo->query("SELECT c.nombre as curso, a.nombre as materia, u.nombre as profe, u.foto 
                     FROM programacion_academica pa 
                     JOIN cursos c ON pa.curso_id = c.id
                     JOIN asignaturas a ON pa.asignatura_id = a.id
                     JOIN usuarios u ON pa.profesor_id = u.id
                     WHERE pa.id = $prog_id")->fetch();

// OBTENER HORARIOS EXISTENTES
$sqlH = "SELECT * FROM horarios WHERE programacion_id = ? 
         ORDER BY FIELD(dia, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'), hora_inicio";
$stmtH = $pdo->prepare($sqlH);
$stmtH->execute([$prog_id]);
$horarios_actuales = $stmtH->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Configurar Horario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

    <?php include '../includes/sidebar_admin.php'; ?>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <div class="bg-white p-3 rounded-circle shadow-sm me-3 text-primary">
                    <i class="bi bi-calendar-week fs-3"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0">Gestión de Horario</h3>
                    <p class="text-muted mb-0">
                        <span class="badge bg-primary"><?php echo $info['curso']; ?></span> 
                        <?php echo $info['materia']; ?> 
                        <small class="text-muted ms-2">| Prof. <?php echo $info['profe']; ?></small>
                    </p>
                </div>
            </div>
            <a href="asignacion.php" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>

        <?php if($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_msg; ?> shadow-sm border-0 mb-4 d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-2"></i> <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            
            <div class="col-lg-4 mb-4">
                <div class="card shadow border-0 sticky-top" style="top: 20px; z-index: 1;">
                    
                    <div class="card-header <?php echo $datos_editar ? 'bg-warning text-dark' : 'bg-primary text-white'; ?> py-3">
                        <h5 class="mb-0 fw-bold">
                            <?php echo $datos_editar ? '<i class="bi bi-pencil-square"></i> Editando Bloque' : '<i class="bi bi-plus-circle"></i> Agregar Bloque'; ?>
                        </h5>
                    </div>
                    
                    <div class="card-body p-4">
                        <form method="POST" action="horarios.php?id=<?php echo $prog_id; ?>">
                            <input type="hidden" name="id_actualizar" value="<?php echo $datos_editar['id'] ?? ''; ?>">

                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary small">Día de la Semana</label>
                                <select name="dia" class="form-select" required>
                                    <?php 
                                    $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
                                    foreach($dias as $d): 
                                        $selected = ($datos_editar && $datos_editar['dia'] == $d) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $d; ?>" <?php echo $selected; ?>><?php echo $d; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-bold text-secondary small">Inicio</label>
                                    <input type="time" name="inicio" class="form-control" required
                                           value="<?php echo $datos_editar['hora_inicio'] ?? ''; ?>">
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-bold text-secondary small">Fin</label>
                                    <input type="time" name="fin" class="form-control" required
                                           value="<?php echo $datos_editar['hora_fin'] ?? ''; ?>">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-secondary small">Sala / Aula (Opcional)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-geo-alt"></i></span>
                                    <input type="text" name="aula" class="form-control" placeholder="Ej: Sala 104"
                                           value="<?php echo $datos_editar['aula'] ?? ''; ?>">
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn <?php echo $datos_editar ? 'btn-warning' : 'btn-primary'; ?> fw-bold py-2 shadow-sm">
                                    <?php echo $datos_editar ? 'GUARDAR CAMBIOS' : 'AGREGAR AL HORARIO'; ?>
                                </button>
                                
                                <?php if($datos_editar): ?>
                                    <a href="horarios.php?id=<?php echo $prog_id; ?>" class="btn btn-outline-secondary">Cancelar Edición</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-secondary">Horario Semanal Cargado</h5>
                        <span class="badge bg-light text-dark border"><?php echo count($horarios_actuales); ?> Bloques</span>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-modern table-hover mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Día</th>
                                        <th>Horario</th>
                                        <th>Sala</th>
                                        <th class="text-end pe-4">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($horarios_actuales) == 0): ?>
                                        <tr><td colspan="4" class="text-center py-5 text-muted">
                                            <i class="bi bi-calendar-x display-4 opacity-25"></i><br>
                                            No hay horarios asignados aún.
                                        </td></tr>
                                    <?php endif; ?>

                                    <?php foreach($horarios_actuales as $h): ?>
                                        <tr class="<?php echo ($datos_editar && $datos_editar['id'] == $h['id']) ? 'table-warning' : ''; ?>">
                                            <td class="ps-4">
                                                <span class="badge bg-info text-dark border border-info-subtle px-3 py-2">
                                                    <?php echo $h['dia']; ?>
                                                </span>
                                            </td>
                                            <td class="fw-bold text-dark">
                                                <i class="bi bi-clock text-muted me-1"></i> 
                                                <?php echo substr($h['hora_inicio'], 0, 5) . " - " . substr($h['hora_fin'], 0, 5); ?>
                                            </td>
                                            <td class="text-secondary">
                                                <?php echo $h['aula'] ? '<i class="bi bi-geo-alt-fill text-danger me-1"></i> '.$h['aula'] : '<span class="text-muted small">N/A</span>'; ?>
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="horarios.php?id=<?php echo $prog_id; ?>&editar=<?php echo $h['id']; ?>" 
                                                   class="btn btn-sm btn-light text-primary border me-1 shadow-sm" title="Editar Bloque">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>

                                                <a href="horarios.php?id=<?php echo $prog_id; ?>&borrar=<?php echo $h['id']; ?>" 
                                                   class="btn btn-sm btn-light text-danger border shadow-sm"
                                                   onclick="return confirm('¿Eliminar este bloque del horario?');"
                                                   title="Eliminar">
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