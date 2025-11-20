<?php
session_start();
require '../config/db.php';
if ($_SESSION['rol'] != 'alumno') { header("Location: ../login.php"); exit; }

$alumno_id = $_SESSION['user_id'];

// BUSCAR CURSO Y HORARIO (Igual que antes, pero traemos el programacion_id y el nombre de la asignatura)
$stmtCurso = $pdo->prepare("SELECT curso_id FROM matriculas WHERE alumno_id = ?");
$stmtCurso->execute([$alumno_id]);
$curso = $stmtCurso->fetch();

if (!$curso) die("No tienes curso asignado.");

$sql = "SELECT h.dia, h.hora_inicio, h.hora_fin, a.nombre as materia, u.nombre as profe, pa.id as prog_id
        FROM horarios h
        JOIN programacion_academica pa ON h.programacion_id = pa.id
        JOIN asignaturas a ON pa.asignatura_id = a.id
        JOIN usuarios u ON pa.profesor_id = u.id
        WHERE pa.curso_id = :cid
        ORDER BY FIELD(h.dia, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'), h.hora_inicio";

$stmt = $pdo->prepare($sql);
$stmt->execute(['cid' => $curso['curso_id']]);
$horario = $stmt->fetchAll(PDO::FETCH_GROUP); 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Mi Horario Interactivo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Hacemos que la tarjeta parezca un botón */
        .clase-card { cursor: pointer; transition: transform 0.2s; }
        .clase-card:hover { transform: scale(1.03); background-color: #e9ecef; }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-4">
        <a href="dashboard.php" class="btn btn-secondary mb-3">&larr; Volver</a>
        <h3>📅 Mi Horario (Haz clic en una clase)</h3>
        
        <div class="row flex-nowrap overflow-auto"> <?php 
            $dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
            foreach ($dias_semana as $dia): 
            ?>
                <div class="col-md-4 col-lg-2 mb-4" style="min-width: 200px;">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-header bg-dark text-white text-center fw-bold">
                            <?php echo $dia; ?>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php if (isset($horario[$dia])): ?>
                                <?php foreach ($horario[$dia] as $clase): ?>
                                    <div class="list-group-item clase-card p-3" 
                                         onclick="verDetalle(<?php echo $clase['prog_id']; ?>, '<?php echo $clase['materia']; ?>')">
                                        
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1 text-primary fw-bold"><?php echo $clase['materia']; ?></h6>
                                        </div>
                                        <small class="text-muted d-block mb-1"><?php echo substr($clase['hora_inicio'], 0, 5) . " - " . substr($clase['hora_fin'], 0, 5); ?></small>
                                        <small class="text-muted fst-italic">Prof. <?php echo $clase['profe']; ?></small>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="list-group-item text-muted text-center py-4 bg-light">Libre</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="modal fade" id="modalDetalle" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="tituloModal">Cargando...</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="contenidoModal">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p>Consultando notas y asistencia...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" id="btnIrCurso" class="btn btn-outline-primary w-100">Ir al Aula Virtual</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Inicializamos el Modal de Bootstrap
        const myModal = new bootstrap.Modal(document.getElementById('modalDetalle'));

        function verDetalle(idProgramacion, nombreMateria) {
            // 1. Ponemos título y link
            document.getElementById('tituloModal').innerText = nombreMateria;
            document.getElementById('btnIrCurso').href = 'ver_curso.php?id=' + idProgramacion;
            
            // 2. Limpiamos el contenido anterior y mostramos el modal cargando
            document.getElementById('contenidoModal').innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary"></div><p>Cargando datos...</p></div>';
            myModal.show();

            // 3. AJAX: Pedimos los datos al archivo PHP
            let formData = new FormData();
            formData.append('id', idProgramacion);

            fetch('ajax_detalle_materia.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                // 4. Inyectamos el HTML que nos devolvió PHP dentro del modal
                document.getElementById('contenidoModal').innerHTML = html;
            })
            .catch(error => {
                document.getElementById('contenidoModal').innerHTML = '<p class="text-danger">Error al cargar datos.</p>';
            });
        }
    </script>
</body>
</html>