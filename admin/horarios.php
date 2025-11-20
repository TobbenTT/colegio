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
$datos_editar = null; // Variable para almacenar datos si estamos editando

// --- LÓGICA DE EDICIÓN (Paso 1: Cargar datos al formulario) ---
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
    $id_actualizar = $_POST['id_actualizar']; // Campo oculto

    if (!empty($id_actualizar)) {
        // CASO A: ACTUALIZAR (UPDATE)
        $sql = "UPDATE horarios SET dia=?, hora_inicio=?, hora_fin=?, aula=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$dia, $inicio, $fin, $aula, $id_actualizar])) {
            $mensaje = "Bloque actualizado correctamente.";
            // Limpiamos $datos_editar para volver al modo "Agregar"
            $datos_editar = null; 
        }
    } else {
        // CASO B: CREAR NUEVO (INSERT)
        $sql = "INSERT INTO horarios (programacion_id, dia, hora_inicio, hora_fin, aula) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$prog_id, $dia, $inicio, $fin, $aula])) {
            $mensaje = "Bloque agregado correctamente.";
        }
    }
}

// --- LÓGICA DE BORRADO ---
if (isset($_GET['borrar'])) {
    $id_horario = $_GET['borrar'];
    $pdo->prepare("DELETE FROM horarios WHERE id = ?")->execute([$id_horario]);
    header("Location: horarios.php?id=" . $prog_id);
    exit;
}

// OBTENER DATOS GENERALES
$info = $pdo->query("SELECT c.nombre as curso, a.nombre as materia, u.nombre as profe 
                     FROM programacion_academica pa 
                     JOIN cursos c ON pa.curso_id = c.id
                     JOIN asignaturas a ON pa.asignatura_id = a.id
                     JOIN usuarios u ON pa.profesor_id = u.id
                     WHERE pa.id = $prog_id")->fetch();

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
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3>Gestión de Horario</h3>
                <h5 class="text-muted">
                    <?php echo $info['curso'] . " - " . $info['materia']; ?> 
                    <small>(<?php echo $info['profe']; ?>)</small>
                </h5>
            </div>
            <a href="asignacion.php" class="btn btn-secondary">Volver a Asignaciones</a>
        </div>

        <?php if($mensaje): ?><div class="alert alert-success"><?php echo $mensaje; ?></div><?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow p-3 <?php echo $datos_editar ? 'border-warning' : ''; ?>">
                    <h5 class="card-title <?php echo $datos_editar ? 'text-warning' : 'text-primary'; ?>">
                        <?php echo $datos_editar ? '✏️ Editando Bloque' : '➕ Agregar Bloque'; ?>
                    </h5>
                    
                    <form method="POST" action="horarios.php?id=<?php echo $prog_id; ?>">
                        <input type="hidden" name="id_actualizar" value="<?php echo $datos_editar['id'] ?? ''; ?>">

                        <div class="mb-3">
                            <label>Día</label>
                            <select name="dia" class="form-select" required>
                                <?php 
                                $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
                                foreach($dias as $d): 
                                    // Truco para dejar seleccionado el día si estamos editando
                                    $selected = ($datos_editar && $datos_editar['dia'] == $d) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $d; ?>" <?php echo $selected; ?>><?php echo $d; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label>Inicio</label>
                                <input type="time" name="inicio" class="form-control" required
                                       value="<?php echo $datos_editar['hora_inicio'] ?? ''; ?>">
                            </div>
                            <div class="col-6 mb-3">
                                <label>Fin</label>
                                <input type="time" name="fin" class="form-control" required
                                       value="<?php echo $datos_editar['hora_fin'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Sala / Aula (Opcional)</label>
                            <input type="text" name="aula" class="form-control" placeholder="Ej: Sala 104"
                                   value="<?php echo $datos_editar['aula'] ?? ''; ?>">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn <?php echo $datos_editar ? 'btn-warning' : 'btn-success'; ?>">
                                <?php echo $datos_editar ? 'Guardar Cambios' : '+ Agregar Hora'; ?>
                            </button>
                            
                            <?php if($datos_editar): ?>
                                <a href="horarios.php?id=<?php echo $prog_id; ?>" class="btn btn-outline-secondary">Cancelar Edición</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">Horario Semanal Cargado</div>
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Día</th>
                                <th>Horario</th>
                                <th>Sala</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($horarios_actuales) == 0): ?>
                                <tr><td colspan="4" class="text-center text-muted">No hay horarios asignados aún.</td></tr>
                            <?php endif; ?>

                            <?php foreach($horarios_actuales as $h): ?>
                                <tr class="<?php echo ($datos_editar && $datos_editar['id'] == $h['id']) ? 'table-warning' : ''; ?>">
                                    <td class="fw-bold"><?php echo $h['dia']; ?></td>
                                    <td>
                                        <?php echo substr($h['hora_inicio'], 0, 5) . " - " . substr($h['hora_fin'], 0, 5); ?>
                                    </td>
                                    <td><?php echo $h['aula']; ?></td>
                                    <td class="text-end">
                                        <a href="horarios.php?id=<?php echo $prog_id; ?>&editar=<?php echo $h['id']; ?>" 
                                           class="btn btn-sm btn-warning text-dark me-1">
                                            ✏️
                                        </a>

                                        <a href="horarios.php?id=<?php echo $prog_id; ?>&borrar=<?php echo $h['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('¿Eliminar este bloque?');">
                                            🗑️
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
</body>
</html>