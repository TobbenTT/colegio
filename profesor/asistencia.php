<?php
session_start();
require '../config/db.php';
require_once '../includes/funciones.php'; // IMPORTANTE: Para enviar notificaciones

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'profesor') { header("Location: ../login.php"); exit; }

$prog_id = $_GET['id'] ?? null;
if (!$prog_id) die("Falta ID.");

$mensaje = "";

// 1. GUARDAR ASISTENCIA
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fecha = $_POST['fecha'];
    $horario_id = $_POST['horario_id'];
    $asistencias = $_POST['asistencia']; 

    if(empty($horario_id)) {
        $mensaje = "⚠️ Error: Selecciona un bloque horario.";
    } else {
        $pdo->beginTransaction();
        try {
            // Borrar registro previo para evitar duplicados
            $pdo->prepare("DELETE FROM asistencia WHERE programacion_id = ? AND fecha = ? AND horario_id = ?")->execute([$prog_id, $fecha, $horario_id]);
            
            $stmtIns = $pdo->prepare("INSERT INTO asistencia (programacion_id, alumno_id, horario_id, fecha, estado) VALUES (?, ?, ?, ?, ?)");
            
            // Preparar consulta para buscar apoderado (optimización)
            $stmtPapa = $pdo->prepare("SELECT apoderado_id FROM familia WHERE alumno_id = ?");

            foreach ($asistencias as $alumno_id => $estado) {
                // Guardar asistencia
                $stmtIns->execute([$prog_id, $alumno_id, $horario_id, $fecha, $estado]);

                // --- LÓGICA DE NOTIFICACIÓN DE AUSENCIA ---
                if ($estado == 'ausente') {
                    // Buscar al apoderado
                    $stmtPapa->execute([$alumno_id]);
                    $id_papa = $stmtPapa->fetchColumn();

                    if ($id_papa) {
                        $msg = "Alerta de Asistencia: Tu pupilo ha sido marcado como AUSENTE el " . date("d/m/Y", strtotime($fecha));
                        // Enviamos la notificación al papá
                        enviarNotificacion($pdo, $id_papa, $msg, 'dashboard.php');
                    }
                }
                // -----------------------------------------
            }
            $pdo->commit();
            $mensaje = "Asistencia guardada y apoderados notificados (si corresponde).";
        } catch (Exception $e) {
            $pdo->rollBack();
            $mensaje = "Error: " . $e->getMessage();
        }
    }
}

// 2. DATOS DE LA VISTA
$fecha_seleccionada = isset($_GET['fecha_filtro']) ? $_GET['fecha_filtro'] : date('Y-m-d');
$dias_ingles = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$dias_espanol = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
$dia_semana_actual = str_replace($dias_ingles, $dias_espanol, date('l', strtotime($fecha_seleccionada)));

$stmtB = $pdo->prepare("SELECT id, hora_inicio, hora_fin FROM horarios WHERE programacion_id = ? AND dia = ?");
$stmtB->execute([$prog_id, $dia_semana_actual]);
$bloques_disponibles = $stmtB->fetchAll();

$curso = $pdo->query("SELECT c.nombre as curso FROM programacion_academica pa JOIN cursos c ON pa.curso_id = c.id WHERE pa.id=$prog_id")->fetch();
$alumnos = $pdo->prepare("SELECT u.id, u.nombre, u.foto FROM matriculas m JOIN usuarios u ON m.alumno_id = u.id WHERE m.curso_id = (SELECT curso_id FROM programacion_academica WHERE id=?) AND u.rol='alumno' ORDER BY u.nombre");
$alumnos->execute([$prog_id]);
$lista_alumnos = $alumnos->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pasar Lista</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .btn-status { width: 130px; font-weight: bold; border-radius: 20px; transition: all 0.2s; }
        .avatar-small { width: 35px; height: 35px; object-fit: cover; border-radius: 50%; }
    </style>
</head>
<body>

    <?php include '../includes/sidebar_profesor.php'; ?>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Asistencia: <?php echo $curso['curso']; ?></h4>
                <p class="text-muted mb-0 small">Los apoderados serán notificados automáticamente de las ausencias.</p>
            </div>
            <a href="ver_curso.php?id=<?php echo $prog_id; ?>" class="btn btn-outline-secondary rounded-pill">Volver</a>
        </div>

        <?php if($mensaje): ?>
            <div class="alert alert-success shadow-sm border-0 mb-4"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body bg-light rounded-3">
                <form method="GET" id="formFecha" class="row g-3 align-items-center">
                    <input type="hidden" name="id" value="<?php echo $prog_id; ?>">
                    <div class="col-auto"><label class="fw-bold text-secondary">Fecha:</label></div>
                    <div class="col-auto">
                        <input type="date" name="fecha_filtro" class="form-control" value="<?php echo $fecha_seleccionada; ?>" onchange="document.getElementById('formFecha').submit()">
                    </div>
                    <div class="col-auto"><span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?php echo $dia_semana_actual; ?></span></div>
                </form>
            </div>
        </div>

        <form method="POST">
            <input type="hidden" name="fecha" value="<?php echo $fecha_seleccionada; ?>">

            <?php if(count($bloques_disponibles) == 0): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-calendar-x display-1 opacity-25"></i>
                    <h5 class="mt-3">Sin clases programadas</h5>
                    <p>No hay horarios configurados para los <?php echo $dia_semana_actual; ?>s.</p>
                </div>
            <?php else: ?>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Seleccionar Bloque Horario</label>
                    <select name="horario_id" class="form-select form-select-lg border-primary" required>
                        <?php foreach($bloques_disponibles as $bloque): ?>
                            <option value="<?php echo $bloque['id']; ?>">
                                ⏰ <?php echo substr($bloque['hora_inicio'], 0, 5); ?> a <?php echo substr($bloque['hora_fin'], 0, 5); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="card shadow border-0">
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Estudiante</th>
                                    <th class="text-center">Estado (Click para cambiar)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($lista_alumnos as $alu): ?>
                                    <?php $foto = $alu['foto'] ? "../assets/uploads/perfiles/".$alu['foto'] : "https://cdn-icons-png.flaticon.com/512/2995/2995620.png"; ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo $foto; ?>" class="avatar-small me-3">
                                                <span class="fw-bold text-dark"><?php echo $alu['nombre']; ?></span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <input type="hidden" id="input_<?php echo $alu['id']; ?>" name="asistencia[<?php echo $alu['id']; ?>]" value="presente">
                                            <button type="button" id="btn_<?php echo $alu['id']; ?>" class="btn btn-success btn-status" onclick="cambiarEstado(<?php echo $alu['id']; ?>)">
                                                <i class="bi bi-check-circle-fill"></i> PRESENTE
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-grid mt-4 mb-5">
                    <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm">
                        <i class="bi bi-save"></i> GUARDAR REGISTRO
                    </button>
                </div>

            <?php endif; ?>
        </form>
    </div>

    <script>
        function cambiarEstado(id) {
            const input = document.getElementById('input_' + id);
            const btn = document.getElementById('btn_' + id);
            
            if (input.value === 'presente') {
                input.value = 'ausente';
                btn.className = 'btn btn-danger btn-status';
                btn.innerHTML = '<i class="bi bi-x-circle-fill"></i> AUSENTE';
            } else if (input.value === 'ausente') {
                input.value = 'atrasado';
                btn.className = 'btn btn-warning text-dark btn-status';
                btn.innerHTML = '<i class="bi bi-clock-history"></i> ATRASO';
            } else {
                input.value = 'presente';
                btn.className = 'btn btn-success btn-status';
                btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> PRESENTE';
            }
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>