<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'profesor') { header("Location: ../login.php"); exit; }

$prog_id = $_GET['id'] ?? null;
if (!$prog_id) die("Falta ID del curso");

$mensaje = "";

// --- 1. LÓGICA PARA GUARDAR (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fecha = $_POST['fecha'];
    $horario_id = $_POST['horario_id'];
    $asistencias = $_POST['asistencia']; // Array [id_alumno => estado]

    if(empty($horario_id)) {
        $mensaje = "Error: Debes seleccionar un bloque horario.";
    } else {
        $pdo->beginTransaction();
        try {
            // Borrar anterior
            $stmtDel = $pdo->prepare("DELETE FROM asistencia WHERE programacion_id = ? AND fecha = ? AND horario_id = ?");
            $stmtDel->execute([$prog_id, $fecha, $horario_id]);

            // Insertar nueva
            $stmtIns = $pdo->prepare("INSERT INTO asistencia (programacion_id, alumno_id, horario_id, fecha, estado) VALUES (?, ?, ?, ?, ?)");
            foreach ($asistencias as $alumno_id => $estado) {
                $stmtIns->execute([$prog_id, $alumno_id, $horario_id, $fecha, $estado]);
            }
            $pdo->commit();
            $mensaje = "Asistencia guardada correctamente.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $mensaje = "Error: " . $e->getMessage();
        }
    }
}

// --- 2. OBTENER DATOS ---
$fecha_seleccionada = isset($_GET['fecha_filtro']) ? $_GET['fecha_filtro'] : date('Y-m-d');

// Traducción de día
$dias_ingles = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$dias_espanol = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
$dia_nombre_ingles = date('l', strtotime($fecha_seleccionada));
$dia_semana_actual = str_replace($dias_ingles, $dias_espanol, $dia_nombre_ingles);

// Bloques horarios
$sqlBloques = "SELECT id, hora_inicio, hora_fin FROM horarios WHERE programacion_id = ? AND dia = ?";
$stmtB = $pdo->prepare($sqlBloques);
$stmtB->execute([$prog_id, $dia_semana_actual]);
$bloques_disponibles = $stmtB->fetchAll();

// Datos del curso
$curso = $pdo->query("SELECT curso_id, (SELECT nombre FROM cursos WHERE id=programacion_academica.curso_id) as nombre FROM programacion_academica WHERE id=$prog_id")->fetch();

// Alumnos
$alumnos = $pdo->prepare("SELECT u.id, u.nombre FROM matriculas m JOIN usuarios u ON m.alumno_id = u.id WHERE m.curso_id = ? AND u.rol='alumno' ORDER BY u.nombre ASC");
$alumnos->execute([$curso['curso_id']]);
$lista_alumnos = $alumnos->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Tomar Asistencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Estilo para que el botón se vea grande y fácil de cliquear */
        .btn-asistencia {
            width: 140px;
            font-weight: bold;
            transition: all 0.2s;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-4 pb-5">
        <div class="d-flex justify-content-between mb-3">
            <h3>Pasar Lista: <?php echo $curso['nombre']; ?></h3>
            <a href="ver_curso.php?id=<?php echo $prog_id; ?>" class="btn btn-secondary">Volver</a>
        </div>

        <?php if($mensaje): ?><div class="alert alert-success"><?php echo $mensaje; ?></div><?php endif; ?>

        <div class="card shadow mb-4 p-3">
            <form method="GET" id="formFecha" class="row g-3 align-items-end">
                <input type="hidden" name="id" value="<?php echo $prog_id; ?>">
                <div class="col-md-4">
                    <label class="fw-bold">Selecciona Fecha:</label>
                    <input type="date" name="fecha_filtro" class="form-control" 
                           value="<?php echo $fecha_seleccionada; ?>" 
                           onchange="document.getElementById('formFecha').submit()">
                </div>
                <div class="col-md-8">
                    <small class="text-muted">Día: <strong><?php echo $dia_semana_actual; ?></strong></small>
                </div>
            </form>
        </div>

        <form method="POST">
            <input type="hidden" name="fecha" value="<?php echo $fecha_seleccionada; ?>">

            <?php if(count($bloques_disponibles) == 0): ?>
                <div class="alert alert-warning text-center">
                    ⚠️ No hay clases programadas para este día (<?php echo $dia_semana_actual; ?>).
                </div>
            <?php else: ?>
                
                <div class="card mb-3 border-primary">
                    <div class="card-body bg-white">
                        <label class="fw-bold text-primary">Selecciona el Bloque:</label>
                        <select name="horario_id" class="form-select mt-2" required>
                            <?php foreach($bloques_disponibles as $bloque): ?>
                                <option value="<?php echo $bloque['id']; ?>">
                                    De <?php echo substr($bloque['hora_inicio'], 0, 5); ?> a <?php echo substr($bloque['hora_fin'], 0, 5); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="card shadow">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <span>Alumnos</span>
                        <button type="submit" class="btn btn-light btn-sm fw-bold">💾 GUARDAR TODO</button>
                    </div>
                    
                    <table class="table table-modern align-middle mb-0">
                        <thead class="table-secondary">
                            <tr>
                                <th>Estudiante</th>
                                <th class="text-center">Estado (Click para cambiar)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($lista_alumnos as $alu): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo $alu['nombre']; ?></td>
                                    <td class="text-center">
                                        <input type="hidden" 
                                               id="input_<?php echo $alu['id']; ?>" 
                                               name="asistencia[<?php echo $alu['id']; ?>]" 
                                               value="presente">

                                        <button type="button" 
                                                id="btn_<?php echo $alu['id']; ?>"
                                                class="btn btn-success btn-asistencia"
                                                onclick="cambiarEstado(<?php echo $alu['id']; ?>)">
                                            PRESENTE
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="d-grid mt-3">
                    <button type="submit" class="btn btn-primary btn-lg shadow">💾 Guardar Asistencia</button>
                </div>

            <?php endif; ?>
        </form>
    </div>

    <script>
        function cambiarEstado(idAlumno) {
            // Obtenemos el input oculto y el botón visual
            const input = document.getElementById('input_' + idAlumno);
            const btn = document.getElementById('btn_' + idAlumno);
            
            const estadoActual = input.value;

            // LÓGICA DE CICLO: Presente -> Ausente -> Atrasado -> Presente
            if (estadoActual === 'presente') {
                // Cambiar a AUSENTE
                input.value = 'ausente';
                btn.className = 'btn btn-danger btn-asistencia'; // Rojo
                btn.innerText = 'AUSENTE';
            } 
            else if (estadoActual === 'ausente') {
                // Cambiar a ATRASADO
                input.value = 'atrasado';
                btn.className = 'btn btn-warning btn-asistencia'; // Amarillo
                btn.innerText = 'ATRASADO';
            } 
            else {
                // Volver a PRESENTE
                input.value = 'presente';
                btn.className = 'btn btn-success btn-asistencia'; // Verde
                btn.innerText = 'PRESENTE';
            }
        }
    </script>
</body>
</html>