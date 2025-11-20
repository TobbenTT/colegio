<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'profesor') { header("Location: ../login.php"); exit; }

if (!isset($_GET['id'])) die("Falta ID.");
$actividad_id = $_GET['id'];

// 1. GUARDAR NOTA
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['calificar'])) {
    $entrega_id = $_POST['entrega_id'];
    $nota = $_POST['nota'];
    $comentario = $_POST['comentario'];

    $sqlUpdate = "UPDATE entregas SET nota = :nota, comentario_profesor = :com WHERE id = :id";
    $stmtUp = $pdo->prepare($sqlUpdate);
    if ($stmtUp->execute(['nota' => $nota, 'com' => $comentario, 'id' => $entrega_id])) {
        $mensaje = "Calificación guardada.";
    }
}

// 2. DATOS ACTIVIDAD
$stmtAct = $pdo->prepare("SELECT * FROM actividades WHERE id = ?");
$stmtAct->execute([$actividad_id]);
$actividad = $stmtAct->fetch();

// 3. LISTA DE ENTREGAS
$sqlEntregas = "SELECT e.*, u.nombre as alumno, u.foto 
                FROM entregas e 
                JOIN usuarios u ON e.alumno_id = u.id 
                WHERE e.actividad_id = ? 
                ORDER BY e.fecha_entrega DESC";
$stmtEnt = $pdo->prepare($sqlEntregas);
$stmtEnt->execute([$actividad_id]);
$entregas = $stmtEnt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Revisión de Tareas</title>
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
                <h4 class="fw-bold mb-1">Evaluación: <?php echo $actividad['titulo']; ?></h4>
                <p class="text-muted mb-0 small">Revisa los archivos y asigna la calificación.</p>
            </div>
            <a href="ver_curso.php?id=<?php echo $actividad['programacion_id']; ?>" class="btn btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-left"></i> Volver al Curso
            </a>
        </div>

        <?php if($mensaje): ?>
            <div class="alert alert-success shadow-sm border-0 mb-4"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <div class="card shadow border-0">
            <div class="card-body p-0">
                <?php if(count($entregas) == 0): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-folder2-open display-1 text-muted opacity-25"></i>
                        <p class="mt-3 text-muted">Aún no hay entregas de alumnos.</p>
                    </div>
                <?php else: ?>
                    
                    <div class="table-responsive">
                        <table class="table table-modern table-hover mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Alumno</th>
                                    <th>Fecha Entrega</th>
                                    <th>Archivo</th>
                                    <th>Estado</th>
                                    <th class="text-end pe-4">Calificación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($entregas as $ent): ?>
                                    <?php 
                                        $foto = $ent['foto'] ? "../assets/uploads/perfiles/".$ent['foto'] : "https://cdn-icons-png.flaticon.com/512/2995/2995620.png";
                                        $tiene_nota = !empty($ent['nota']);
                                    ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo $foto; ?>" width="35" height="35" class="rounded-circle me-3 border">
                                                <span class="fw-bold text-dark"><?php echo $ent['alumno']; ?></span>
                                            </div>
                                        </td>
                                        <td class="text-muted small">
                                            <?php echo date("d/m H:i", strtotime($ent['fecha_entrega'])); ?>
                                            <?php 
                                                // Si entregó después de la fecha límite (opcional, lógica simple)
                                                if($actividad['fecha_limite'] && $ent['fecha_entrega'] > $actividad['fecha_limite']) {
                                                    echo '<span class="text-danger ms-1">(Tardío)</span>';
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <a href="../assets/uploads/<?php echo $ent['archivo_entrega']; ?>" target="_blank" class="btn btn-sm btn-light border text-primary">
                                                <i class="bi bi-download"></i> Ver Tarea
                                            </a>
                                        </td>
                                        <td>
                                            <?php if($tiene_nota): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 rounded-pill">Calificado</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 rounded-pill">Pendiente</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-primary px-3 rounded-pill" 
                                                    onclick="abrirCalificar(<?php echo $ent['id']; ?>, '<?php echo $ent['nota']; ?>', '<?php echo htmlspecialchars($ent['comentario_profesor']); ?>')">
                                                <?php echo $tiene_nota ? 'Editar Nota' : 'Calificar'; ?>
                                            </button>
                                            <?php if($tiene_nota): ?>
                                                <span class="fw-bold fs-5 ms-2 text-dark"><?php echo $ent['nota']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCalificar" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">Asignar Nota</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="entrega_id" id="inputEntregaId">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Nota (1.0 - 7.0)</label>
                            <input type="number" name="nota" id="inputNota" class="form-control form-control-lg text-center fw-bold" step="0.1" min="1.0" max="7.0" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small text-muted">Feedback / Comentario</label>
                            <textarea name="comentario" id="inputComentario" class="form-control" rows="3" placeholder="Muy buen trabajo..."></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="calificar" class="btn btn-success fw-bold">GUARDAR</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script para pasar datos al modal sin recargar
        function abrirCalificar(id, nota, comentario) {
            document.getElementById('inputEntregaId').value = id;
            document.getElementById('inputNota').value = nota;
            document.getElementById('inputComentario').value = comentario;
            
            var myModal = new bootstrap.Modal(document.getElementById('modalCalificar'));
            myModal.show();
        }
    </script>

</body>
</html>