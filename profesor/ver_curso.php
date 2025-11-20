<?php
session_start();
// IMPORTANTE: Usamos ../ para salir de la carpeta 'profesor' y buscar 'config'
require '../config/db.php';

// 1. SEGURIDAD
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'profesor') {
    header("Location: ../login.php"); exit;
}

if (!isset($_GET['id'])) { die("Error: Falta ID."); }
$id_programacion = $_GET['id'];
$profesor_id = $_SESSION['user_id'];

// Verificar que el curso pertenece al profesor
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

// 2. LÓGICA PARA SUBIR ARCHIVOS (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_actividad'])) {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $tipo = $_POST['tipo'];
    $fecha_limite = !empty($_POST['fecha_limite']) ? $_POST['fecha_limite'] : NULL;
    
    // Procesar Archivo
    $ruta_archivo = NULL;
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0) {
        $nombre_real = $_FILES['archivo']['name'];
        $nombre_db = time() . "_" . $nombre_real;
        
        // RUTA ACTUALIZADA: ../assets/uploads/
        $directorio_destino = "../assets/uploads/";
        
        // Crear carpeta si no existe
        if (!file_exists($directorio_destino)) {
            mkdir($directorio_destino, 0777, true);
        }

        $ruta_destino = $directorio_destino . $nombre_db;
        
        if (move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_destino)) {
            $ruta_archivo = $nombre_db;
        }
    }

    // Insertar en BD
    $sqlInsert = "INSERT INTO actividades (programacion_id, titulo, descripcion, archivo_adjunto, tipo, fecha_limite) 
                  VALUES (:prog_id, :tit, :desc, :arch, :tipo, :fecha)";
    $stmtInsert = $pdo->prepare($sqlInsert);
    if ($stmtInsert->execute([
        'prog_id' => $id_programacion,
        'tit' => $titulo,
        'desc' => $descripcion,
        'arch' => $ruta_archivo,
        'tipo' => $tipo,
        'fecha' => $fecha_limite
    ])) {
        $mensaje = "Actividad publicada correctamente.";
    }
}

// 3. OBTENER LISTADO DE ACTIVIDADES
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
    
    <div class="container mt-4 mb-5">

        <div class="course-hero d-flex justify-content-between align-items-center position-relative bg-dark" style="z-index: 1;">
            <div class="position-relative" style="z-index: 10;">
                <span class="badge bg-warning text-dark mb-2">Curso 2025</span>
                <h1 class="display-5 fw-bold"><?php echo $datos_curso['asignatura']; ?></h1>
                <p class="lead mb-0 opacity-75"><?php echo $datos_curso['curso']; ?> | Profesor Titular</p>
            </div>
            
            <div class="text-end position-relative" style="z-index: 10;">
                <a href="dashboard.php" class="btn btn-secondary btn-sm mb-2 position-relative">Volver</a><br>
                <a href="asistencia.php?id=<?php echo $id_programacion; ?>" class="btn btn-light text-primary fw-bold me-2">
                    <i class="bi bi-clipboard-check"></i> Asistencia
                </a>
                <a href="anotaciones.php?id=<?php echo $id_programacion; ?>" class="btn btn-light text-primary fw-bold">
                    <i class="bi bi-journal-text"></i> Anotaciones
                </a>
            </div>
        </div>

        <div class="row">
            
            <div class="col-md-4">
                <div class="card shadow-sm sticky-top" style="top: 20px; z-index: 1;">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-plus-circle"></i> Crear Contenido</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-floating mb-2">
                                <input type="text" name="titulo" class="form-control" id="floatingTit" placeholder="Título" required>
                                <label for="floatingTit">Título de la actividad</label>
                            </div>
                            
                            <div class="form-floating mb-2">
                                <textarea name="descripcion" class="form-control" id="floatingDesc" placeholder="Desc" style="height: 80px"></textarea>
                                <label for="floatingDesc">Instrucciones</label>
                            </div>

                            <div class="mb-2">
                                <select name="tipo" class="form-select fw-bold text-secondary">
                                    <option value="material">📘 Material de Lectura</option>
                                    <option value="tarea">📝 Tarea con Entrega</option>
                                </select>
                            </div>

                            <div class="mb-2">
                                <label class="small text-muted">Fecha Límite (Solo tareas)</label>
                                <input type="datetime-local" name="fecha_limite" class="form-control">
                            </div>

                            <div class="mb-3">
                                <input type="file" name="archivo" class="form-control form-control-sm">
                            </div>

                            <button type="submit" name="crear_actividad" class="btn btn-primary w-100 fw-bold py-2">
                                PUBLICAR AHORA
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <h4 class="mb-4 text-muted fw-bold">Cronología de Clases</h4>
                
                <?php if (count($actividades) == 0): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted opacity-25"></i>
                        <p class="mt-3 text-muted">No hay contenido publicado aún.</p>
                    </div>
                <?php else: ?>
                    
                    <?php foreach ($actividades as $act): ?>
                        <div class="card mb-4 activity-card type-<?php echo $act['tipo']; ?>">
                            
                            <div class="file-icon">
                                <?php if($act['tipo'] == 'material'): ?>
                                    <i class="bi bi-file-earmark-pdf-fill"></i>
                                <?php else: ?>
                                    <i class="bi bi-pencil-square"></i>
                                <?php endif; ?>
                            </div>

                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($act['titulo']); ?></h5>
                                    <small class="text-muted"><?php echo date("d M", strtotime($act['created_at'])); ?></small>
                                </div>
                                
                                <p class="text-muted mb-2 small"><?php echo htmlspecialchars($act['descripcion']); ?></p>

                                <div class="d-flex gap-2">
                                    <?php if($act['archivo_adjunto']): ?>
                                        <a href="../assets/uploads/<?php echo $act['archivo_adjunto']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                            <i class="bi bi-download"></i> Descargar
                                        </a>
                                    <?php endif; ?>

                                    <?php if($act['tipo'] == 'tarea'): ?>
                                        <a href="revisar.php?id=<?php echo $act['id']; ?>" class="btn btn-sm btn-primary rounded-pill px-3">
                                            Revisar Entregas <i class="bi bi-arrow-right"></i>
                                        </a>
                                        <?php if($act['fecha_limite']): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill align-self-center">
                                                <i class="bi bi-clock"></i> <?php echo date("d/m H:i", strtotime($act['fecha_limite'])); ?>
                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>
            </div>

        </div>
    </div>

</body>
</html>