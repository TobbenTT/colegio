<?php
session_start();
require '../config/db.php';
require_once '../includes/funciones.php'; // IMPORTANTE: Incluir funciones

// 1. SEGURIDAD
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'profesor') {
    header("Location: ../login.php"); exit;
}

if (!isset($_GET['id'])) { die("Error: Falta ID."); }
$id_programacion = $_GET['id'];
$profesor_id = $_SESSION['user_id'];

// Verificar curso
$sql = "SELECT c.nombre as curso, a.nombre as asignatura 
        FROM programacion_academica pa
        INNER JOIN cursos c ON pa.curso_id = c.id
        INNER JOIN asignaturas a ON pa.asignatura_id = a.id
        WHERE pa.id = :id_prog AND pa.profesor_id = :profe_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id_prog' => $id_programacion, 'profe_id' => $profesor_id]);
$datos_curso = $stmt->fetch();

if (!$datos_curso) { die("Acceso denegado."); }

$mensaje = "";

// 2. SUBIR CONTENIDO (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_actividad'])) {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $tipo = $_POST['tipo'];
    $fecha_limite = !empty($_POST['fecha_limite']) ? $_POST['fecha_limite'] : NULL;
    
    $ruta_archivo = NULL;
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0) {
        $nombre_real = $_FILES['archivo']['name'];
        $nombre_db = time() . "_" . $nombre_real;
        $directorio_destino = "../assets/uploads/";
        
        if (!file_exists($directorio_destino)) mkdir($directorio_destino, 0777, true);

        if (move_uploaded_file($_FILES['archivo']['tmp_name'], $directorio_destino . $nombre_db)) {
            $ruta_archivo = $nombre_db;
        }
    }

    $sqlInsert = "INSERT INTO actividades (programacion_id, titulo, descripcion, archivo_adjunto, tipo, fecha_limite) 
                  VALUES (:prog_id, :tit, :desc, :arch, :tipo, :fecha)";
    $stmtInsert = $pdo->prepare($sqlInsert);
    
    if ($stmtInsert->execute([
        'prog_id' => $id_programacion, 'tit' => $titulo, 'desc' => $descripcion, 
        'arch' => $ruta_archivo, 'tipo' => $tipo, 'fecha' => $fecha_limite
    ])) {
        // --- LÓGICA DE NOTIFICACIÓN MASIVA ---
        // 1. Buscamos a los alumnos del curso
        $stmtAlumnos = $pdo->prepare("SELECT alumno_id FROM matriculas WHERE curso_id = (SELECT curso_id FROM programacion_academica WHERE id = ?)");
        $stmtAlumnos->execute([$id_programacion]);
        $lista_alumnos = $stmtAlumnos->fetchAll();

        // 2. Preparamos mensaje
        $msg_notif = "Nuevo contenido en " . $datos_curso['asignatura'] . ": " . $titulo;
        $link_notif = "ver_curso.php?id=" . $id_programacion;

        // 3. Enviamos a todos
        foreach($lista_alumnos as $alum) {
            enviarNotificacion($pdo, $alum['alumno_id'], $msg_notif, $link_notif);
        }
        // -------------------------------------

        $mensaje = "Contenido publicado y notificaciones enviadas.";
    }
}

// 3. LISTAR ACTIVIDADES
$sqlActividades = "SELECT * FROM actividades WHERE programacion_id = :id ORDER BY created_at DESC";
$stmtAct = $pdo->prepare($sqlActividades);
$stmtAct->execute(['id' => $id_programacion]);
$actividades = $stmtAct->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Aula Virtual</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    
    <?php include '../includes/sidebar_profesor.php'; ?>

    <div class="main-content">
        
        <div class="course-hero d-flex justify-content-between align-items-center">
            <div style="position: relative; z-index: 2;">
                <span class="badge bg-warning text-dark mb-2">Curso 2025</span>
                <h1 class="display-5 fw-bold"><?php echo $datos_curso['asignatura']; ?></h1>
                <p class="lead mb-0 opacity-75"><?php echo $datos_curso['curso']; ?> | Panel Docente</p>
            </div>
            <div class="text-end" style="position: relative; z-index: 2;">
                <a href="dashboard.php" class="btn btn-outline-light btn-sm mb-2">Volver</a><br>
                <a href="asistencia.php?id=<?php echo $id_programacion; ?>" class="btn btn-light text-primary fw-bold me-2">
                    <i class="bi bi-clipboard-check"></i> Asistencia
                </a>
                <a href="anotaciones.php?id=<?php echo $id_programacion; ?>" class="btn btn-light text-primary fw-bold">
                    <i class="bi bi-journal-text"></i> Anotaciones
                </a>
            </div>
        </div>

        <?php if($mensaje): ?>
            <div class="alert alert-success shadow-sm border-0"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <div class="row mt-4">
            
            <div class="col-md-4">
                <div class="card shadow-sm sticky-top" style="top: 20px; z-index: 1;">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-plus-circle"></i> Nuevo Contenido</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-floating mb-2">
                                <input type="text" name="titulo" class="form-control" id="ftit" required>
                                <label for="ftit">Título</label>
                            </div>
                            <div class="form-floating mb-2">
                                <textarea name="descripcion" class="form-control" id="fdesc" style="height: 80px"></textarea>
                                <label for="fdesc">Descripción / Instrucciones</label>
                            </div>
                            
                            <div class="mb-2">
                                <label class="small text-muted fw-bold">Tipo de Actividad</label>
                                <select name="tipo" class="form-select fw-bold text-dark" id="tipoSelect" onchange="toggleFecha()">
                                    <option value="material">📘 Material de Lectura</option>
                                    <option value="tarea">📝 Tarea (Requiere entrega)</option>
                                    <option value="prueba">📅 Prueba / Examen</option>
                                </select>
                            </div>

                            <div class="mb-2">
                                <label class="small text-muted" id="labelFecha">Fecha Límite / Fecha Prueba</label>
                                <input type="datetime-local" name="fecha_limite" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="small text-muted">Adjuntar Archivo (Opcional)</label>
                                <input type="file" name="archivo" class="form-control form-control-sm">
                            </div>

                            <button type="submit" name="crear_actividad" class="btn btn-primary w-100 fw-bold py-2">PUBLICAR</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <h4 class="mb-4 text-muted fw-bold">Contenido Publicado</h4>
                
                <?php if (count($actividades) == 0): ?>
                    <div class="alert alert-light text-center text-muted">No hay contenido aún.</div>
                <?php else: ?>
                    <?php foreach ($actividades as $act): ?>
                        <?php 
                            $borderClass = "type-material";
                            $icon = '<i class="bi bi-file-earmark-text text-primary"></i>';
                            $badge = '<span class="badge bg-primary-subtle text-primary">Material</span>';
                            
                            if($act['tipo'] == 'tarea') {
                                $borderClass = "type-tarea";
                                $icon = '<i class="bi bi-pencil-square text-danger"></i>';
                                $badge = '<span class="badge bg-danger-subtle text-danger">Tarea</span>';
                            } elseif($act['tipo'] == 'prueba') {
                                $borderClass = "type-tarea"; 
                                $icon = '<i class="bi bi-exclamation-diamond-fill text-warning"></i>';
                                $badge = '<span class="badge bg-warning text-dark">PRUEBA / EXAMEN</span>';
                            }
                        ?>

                        <div class="card mb-3 activity-card shadow-sm <?php echo $borderClass; ?>">
                            <div class="file-icon fs-1 me-3"><?php echo $icon; ?></div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <h5 class="fw-bold mb-1"><?php echo $act['titulo']; ?></h5>
                                    <?php echo $badge; ?>
                                </div>
                                <p class="text-muted small mb-2"><?php echo $act['descripcion']; ?></p>
                                
                                <div class="d-flex gap-2 align-items-center">
                                    <?php if($act['archivo_adjunto']): ?>
                                        <a href="../assets/uploads/<?php echo $act['archivo_adjunto']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill">
                                            <i class="bi bi-download"></i> Archivo
                                        </a>
                                    <?php endif; ?>

                                    <?php if($act['fecha_limite']): ?>
                                        <small class="text-muted ms-auto">
                                            <i class="bi bi-calendar-event"></i> <?php echo date("d/m H:i", strtotime($act['fecha_limite'])); ?>
                                        </small>
                                    <?php endif; ?>

                                    <?php if($act['tipo'] == 'tarea'): ?>
                                        <a href="revisar.php?id=<?php echo $act['id']; ?>" class="btn btn-sm btn-primary rounded-pill ms-2">Revisar Entregas</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleFecha() {
            let tipo = document.getElementById('tipoSelect').value;
            let label = document.getElementById('labelFecha');
            if(tipo === 'prueba') label.innerText = "Fecha y Hora del Examen";
            else if(tipo === 'tarea') label.innerText = "Fecha Límite de Entrega";
            else label.innerText = "Fecha (Opcional)";
        }
    </script>
</body>
</html>