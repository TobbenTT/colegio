<?php
session_start();
require '../config/db.php';

// 1. Seguridad
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'alumno') { header("Location: ../login.php"); exit; }

$alumno_id = $_SESSION['user_id'];
$prog_id = $_GET['id'] ?? null;

if (!$prog_id) {
    // Si no hay ID, volver al dashboard
    header("Location: dashboard.php"); exit;
}

$mensaje = "";
$tipo_msg = "";

// 2. LÓGICA DE ENTREGA DE TAREA
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['entrega_archivo'])) {
    $actividad_id = $_POST['actividad_id'];
    $archivo = $_FILES['entrega_archivo'];
    
    if ($archivo['error'] == 0) {
        // Nombre único: tiempo_nombre.ext
        $nombre_final = time() . "_ENTREGA_" . $archivo['name'];
        $destino = "../assets/uploads/" . $nombre_final;
        
        // Crear carpeta si no existe
        if (!file_exists("../assets/uploads/")) mkdir("../assets/uploads/", 0777, true);

        if (move_uploaded_file($archivo['tmp_name'], $destino)) {
            // Guardar en BD
            $sqlInsert = "INSERT INTO entregas (actividad_id, alumno_id, archivo_entrega) VALUES (?, ?, ?)";
            $stmtI = $pdo->prepare($sqlInsert);
            if ($stmtI->execute([$actividad_id, $alumno_id, $nombre_final])) {
                $mensaje = "¡Tarea enviada con éxito! 🚀";
                $tipo_msg = "success";
            }
        } else {
            $mensaje = "Error al subir el archivo.";
            $tipo_msg = "danger";
        }
    }
}

// 3. OBTENER INFO DEL CURSO Y PROFESOR
$sqlInfo = "SELECT a.nombre as materia, c.nombre as curso, u.nombre as profe, u.email as email_profe
            FROM programacion_academica pa
            JOIN asignaturas a ON pa.asignatura_id = a.id
            JOIN cursos c ON pa.curso_id = c.id
            JOIN usuarios u ON pa.profesor_id = u.id
            WHERE pa.id = ?";
$stmtInfo = $pdo->prepare($sqlInfo);
$stmtInfo->execute([$prog_id]);
$info = $stmtInfo->fetch();

if (!$info) die("Curso no encontrado.");

// 4. OBTENER ACTIVIDADES (CRONOLOGÍA)
$sqlAct = "SELECT * FROM actividades WHERE programacion_id = ? ORDER BY created_at DESC";
$stmtAct = $pdo->prepare($sqlAct);
$stmtAct->execute([$prog_id]);
$actividades = $stmtAct->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $info['materia']; ?> - Aula Virtual</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

    <div class="sidebar">
        <div class="logo mb-4"><i class="bi bi-backpack2-fill"></i> Mi Colegio</div>
        <a href="dashboard.php" class="active"><i class="bi bi-grid-fill"></i> <span>Mis Clases</span></a>
        <a href="horario.php"><i class="bi bi-calendar-week"></i> <span>Horario</span></a>
        <a href="asistencia.php"><i class="bi bi-clipboard-check"></i> <span>Asistencia</span></a>
        <a href="mis_anotaciones.php"><i class="bi bi-exclamation-triangle"></i> <span>Hoja de Vida</span></a>
        <a href="perfil.php"><i class="bi bi-person-circle"></i> <span>Mi Perfil</span></a>
        <div class="mt-5"><a href="../logout.php" class="text-danger"><i class="bi bi-box-arrow-left"></i> <span>Salir</span></a></div>
    </div>

    <div class="main-content">
        
        <div class="course-hero mb-4 d-flex justify-content-between align-items-end p-4 rounded-4 text-white shadow-sm">
            <div>
                <span class="badge bg-white text-primary mb-2 text-uppercase px-3 py-2 fw-bold shadow-sm"><?php echo $info['curso']; ?></span>
                <h1 class="display-5 fw-bold mb-1"><?php echo $info['materia']; ?></h1>
                <p class="mb-0 opacity-75"><i class="bi bi-person-video3"></i> Dictado por <?php echo $info['profe']; ?></p>
            </div>
            <div>
                <a href="dashboard.php" class="btn btn-outline-light rounded-pill px-4 fw-bold">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>

        <?php if($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_msg; ?> border-0 shadow-sm mb-4 d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-4 me-2"></i>
                <div><?php echo $mensaje; ?></div>
            </div>
        <?php endif; ?>

        <div class="row">
            
            <div class="col-lg-8">
                
                <?php if(count($actividades) == 0): ?>
                    <div class="card border-0 shadow-sm p-5 text-center">
                        <div class="text-muted opacity-25 mb-3">
                            <i class="bi bi-journal-bookmark display-1"></i>
                        </div>
                        <h4>¡Todo al día!</h4>
                        <p class="text-muted">El profesor aún no ha subido contenido para esta materia.</p>
                    </div>
                <?php else: ?>
                    
                    <h5 class="text-muted fw-bold mb-3 ms-1">Línea de Tiempo</h5>

                    <?php foreach($actividades as $act): ?>
                        <div class="card mb-4 activity-card shadow-sm type-<?php echo $act['tipo']; ?>">
                            
                            <div class="file-icon">
                                <?php if($act['tipo'] == 'tarea'): ?>
                                    <i class="bi bi-pencil-square text-danger"></i>
                                <?php else: ?>
                                    <i class="bi bi-book-half text-primary"></i>
                                <?php endif; ?>
                            </div>

                            <div class="w-100">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge <?php echo ($act['tipo']=='tarea') ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary'; ?> mb-2 text-uppercase" style="font-size: 0.7rem;">
                                            <?php echo ($act['tipo']=='tarea') ? 'Evaluación' : 'Material de Estudio'; ?>
                                        </span>
                                        <h4 class="fw-bold mb-1 text-dark"><?php echo $act['titulo']; ?></h4>
                                    </div>
                                    <small class="text-muted fw-bold"><?php echo date("d M", strtotime($act['created_at'])); ?></small>
                                </div>
                                
                                <p class="text-muted mt-2 mb-3"><?php echo $act['descripcion']; ?></p>

                                <?php if($act['archivo_adjunto']): ?>
                                    <div class="mb-3">
                                        <a href="../assets/uploads/<?php echo $act['archivo_adjunto']; ?>" target="_blank" class="btn btn-light border text-secondary btn-sm rounded-pill px-3">
                                            <i class="bi bi-paperclip"></i> Descargar Adjunto
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <?php if($act['tipo'] == 'tarea'): ?>
                                    
                                    <hr class="border-secondary opacity-10">
                                    
                                    <?php 
                                        // Verificar si ya entregó
                                        $sqlCheck = "SELECT * FROM entregas WHERE actividad_id = ? AND alumno_id = ?";
                                        $stmtCheck = $pdo->prepare($sqlCheck);
                                        $stmtCheck->execute([$act['id'], $alumno_id]);
                                        $entrega = $stmtCheck->fetch();
                                    ?>

                                    <?php if($entrega): ?>
                                        <div class="bg-success-subtle border border-success rounded p-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Tarea Entregada</span>
                                                <small class="text-success"><?php echo date("d/m H:i", strtotime($entrega['fecha_entrega'])); ?></small>
                                            </div>
                                            <?php if($entrega['nota']): ?>
                                                <div class="mt-2 pt-2 border-top border-success-subtle">
                                                    <span class="badge bg-success fs-6">Nota: <?php echo $entrega['nota']; ?></span>
                                                    <?php if($entrega['comentario_profesor']): ?>
                                                        <span class="ms-2 text-success small fst-italic">"<?php echo $entrega['comentario_profesor']; ?>"</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="mt-1 small text-success opacity-75">Esperando calificación...</div>
                                            <?php endif; ?>
                                        </div>

                                    <?php else: ?>
                                        <div class="upload-zone p-3">
                                            <p class="mb-2 fw-bold text-danger small"><i class="bi bi-exclamation-circle"></i> Pendiente de entrega</p>
                                            
                                            <form method="POST" enctype="multipart/form-data" class="d-flex gap-2">
                                                <input type="hidden" name="actividad_id" value="<?php echo $act['id']; ?>">
                                                <input type="file" name="entrega_archivo" class="form-control form-control-sm" required>
                                                <button type="submit" class="btn btn-danger btn-sm fw-bold">
                                                    <i class="bi bi-send"></i> Enviar
                                                </button>
                                            </form>

                                            <?php if($act['fecha_limite']): ?>
                                                <small class="d-block mt-2 text-muted">
                                                    Vence el: <strong><?php echo date("d/m/Y H:i", strtotime($act['fecha_limite'])); ?></strong>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="bi bi-person-video3 fs-1 text-primary"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold"><?php echo $info['profe']; ?></h5>
                        <p class="text-muted small"><?php echo $info['email_profe']; ?></p>
                        <button class="btn btn-outline-primary btn-sm rounded-pill w-100" onclick="alert('Función Mensajería próximamente')">
                            <i class="bi bi-envelope"></i> Enviar Mensaje
                        </button>
                    </div>
                </div>

                <div class="card border-0 shadow-sm bg-primary text-white">
                    <div class="card-body">
                        <h6 class="opacity-75 mb-3">Resumen del Curso</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Evaluaciones</span>
                            <span class="fw-bold">
                                <?php 
                                // Contador rápido en PHP (filtro de arrays)
                                echo count(array_filter($actividades, function($v){ return $v['tipo'] == 'tarea'; }));
                                ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Materiales</span>
                            <span class="fw-bold">
                                <?php 
                                echo count(array_filter($actividades, function($v){ return $v['tipo'] == 'material'; }));
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>