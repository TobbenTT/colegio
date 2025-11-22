<?php
session_start();
require '../config/db.php';
require '../includes/funciones.php';
// 1. Seguridad
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'administrador') { header("Location: ../login.php"); exit; }

$mensaje = "";
$tipo_msg = "";

// Variables para edición
$curso_editar = null;
$asignatura_editar = null;

// --- LÓGICA DE CURSOS ---

// A. CARGAR CURSO PARA EDITAR
if (isset($_GET['editar_curso'])) {
    $stmt = $pdo->prepare("SELECT * FROM cursos WHERE id = ?");
    $stmt->execute([$_GET['editar_curso']]);
    $curso_editar = $stmt->fetch();
}

// B. BORRAR CURSO
if (isset($_GET['borrar_curso'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM cursos WHERE id = ?");
        $stmt->execute([$_GET['borrar_curso']]);
        $mensaje = "Curso eliminado."; $tipo_msg = "warning";
    } catch (Exception $e) {
        $mensaje = "No se puede borrar: Hay alumnos o clases asociadas a este curso."; $tipo_msg = "danger";
    }
}

// C. GUARDAR CURSO (CREAR O ACTUALIZAR)
if (isset($_POST['guardar_curso'])) {
    $nombre = $_POST['nombre_curso'];
    $id = $_POST['id_curso'];

    if (!empty($id)) {
        // UPDATE
        $stmt = $pdo->prepare("UPDATE cursos SET nombre = ? WHERE id = ?");
        if ($stmt->execute([$nombre, $id])) {
            $mensaje = "Curso actualizado correctamente."; $tipo_msg = "info";
            $curso_editar = null; // Limpiar modo edición
        }
    } else {
        // INSERT
        $stmt = $pdo->prepare("INSERT INTO cursos (nombre) VALUES (?)");
        if ($stmt->execute([$nombre])) {
            $mensaje = "Curso creado exitosamente."; $tipo_msg = "success";
        }
    }
}

// --- LÓGICA DE ASIGNATURAS ---

// A. CARGAR ASIGNATURA PARA EDITAR
if (isset($_GET['editar_asignatura'])) {
    $stmt = $pdo->prepare("SELECT * FROM asignaturas WHERE id = ?");
    $stmt->execute([$_GET['editar_asignatura']]);
    $asignatura_editar = $stmt->fetch();
}

// B. BORRAR ASIGNATURA
if (isset($_GET['borrar_asignatura'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM asignaturas WHERE id = ?");
        $stmt->execute([$_GET['borrar_asignatura']]);
        $mensaje = "Asignatura eliminada."; $tipo_msg = "warning";
    } catch (Exception $e) {
        $mensaje = "No se puede borrar: Está siendo usada en la carga académica."; $tipo_msg = "danger";
    }
}

// C. GUARDAR ASIGNATURA (CREAR O ACTUALIZAR)
if (isset($_POST['guardar_asignatura'])) {
    $nombre = $_POST['nombre_asignatura'];
    $id = $_POST['id_asignatura'];

    if (!empty($id)) {
        // UPDATE
        $stmt = $pdo->prepare("UPDATE asignaturas SET nombre = ? WHERE id = ?");
        if ($stmt->execute([$nombre, $id])) {
            $mensaje = "Asignatura actualizada correctamente."; $tipo_msg = "info";
            $asignatura_editar = null;
        }
    } else {
        // INSERT
        $stmt = $pdo->prepare("INSERT INTO asignaturas (nombre) VALUES (?)");
        if ($stmt->execute([$nombre])) {
            $mensaje = "Asignatura creada exitosamente."; $tipo_msg = "success";
        }
    }
}

// Listas Actualizadas
$cursos = $pdo->query("SELECT * FROM cursos ORDER BY nombre")->fetchAll();
$asignaturas = $pdo->query("SELECT * FROM asignaturas ORDER BY nombre")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cursos y Materias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

<?php include '../includes/sidebar_admin.php'; ?>

    <div class="main-content">
        
        <h3 class="fw-bold mb-4"><i class="bi bi-layers-fill"></i> Estructura Académica</h3>

        <?php if($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_msg; ?> shadow-sm border-0 mb-4"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <div class="row">
            
            <div class="col-md-6 mb-4">
                <div class="card shadow border-0 h-100 <?php echo $curso_editar ? 'border-warning border-2' : ''; ?>">
                    <div class="card-header bg-warning text-dark py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <?php echo $curso_editar ? '<i class="bi bi-pencil-square"></i> Editando Curso' : '<i class="bi bi-door-open-fill"></i> Cursos / Aulas'; ?>
                        </h5>
                        <span class="badge bg-white text-dark"><?php echo count($cursos); ?></span>
                    </div>
                    <div class="card-body">
                        
                        <form method="POST" class="d-flex gap-2 mb-4 p-3 bg-light rounded align-items-end">
                            <input type="hidden" name="id_curso" value="<?php echo $curso_editar['id'] ?? ''; ?>">
                            
                            <div class="w-100">
                                <label class="small text-muted fw-bold mb-1">Nombre del Curso</label>
                                <input type="text" name="nombre_curso" class="form-control" 
                                       placeholder="Ej: 1° Medio B" 
                                       value="<?php echo $curso_editar['nombre'] ?? ''; ?>" required>
                            </div>
                            
                            <button type="submit" name="guardar_curso" class="btn <?php echo $curso_editar ? 'btn-primary' : 'btn-warning'; ?> fw-bold">
                                <?php echo $curso_editar ? 'Guardar' : 'Crear'; ?>
                            </button>
                            
                            <?php if($curso_editar): ?>
                                <a href="cursos.php" class="btn btn-secondary"><i class="bi bi-x-lg"></i></a>
                            <?php endif; ?>
                        </form>

                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-modern table-hover mb-0 align-middle">
                                <tbody>
                                    <?php foreach($cursos as $c): ?>
                                        <tr class="<?php echo ($curso_editar && $curso_editar['id'] == $c['id']) ? 'table-active' : ''; ?>">
                                            <td class="fw-bold text-dark ps-4"><?php echo $c['nombre']; ?></td>
                                            <td class="text-end pe-4">
                                                <a href="cursos.php?editar_curso=<?php echo $c['id']; ?>" class="btn btn-sm btn-light text-primary border me-1">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>
                                                <a href="cursos.php?borrar_curso=<?php echo $c['id']; ?>" class="btn btn-sm btn-light text-danger border" onclick="return confirm('¿Eliminar este curso?')">
                                                    <i class="bi bi-trash-fill"></i>
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

            <div class="col-md-6 mb-4">
                <div class="card shadow border-0 h-100 <?php echo $asignatura_editar ? 'border-info border-2' : ''; ?>">
                    <div class="card-header bg-info text-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <?php echo $asignatura_editar ? '<i class="bi bi-pencil-square"></i> Editando Materia' : '<i class="bi bi-book-half"></i> Asignaturas'; ?>
                        </h5>
                        <span class="badge bg-white text-info"><?php echo count($asignaturas); ?></span>
                    </div>
                    <div class="card-body">
                        
                        <form method="POST" class="d-flex gap-2 mb-4 p-3 bg-light rounded align-items-end">
                            <input type="hidden" name="id_asignatura" value="<?php echo $asignatura_editar['id'] ?? ''; ?>">
                            
                            <div class="w-100">
                                <label class="small text-muted fw-bold mb-1">Nombre de Asignatura</label>
                                <input type="text" name="nombre_asignatura" class="form-control" 
                                       placeholder="Ej: Matemáticas" 
                                       value="<?php echo $asignatura_editar['nombre'] ?? ''; ?>" required>
                            </div>

                            <button type="submit" name="guardar_asignatura" class="btn <?php echo $asignatura_editar ? 'btn-primary' : 'btn-info text-white'; ?> fw-bold">
                                <?php echo $asignatura_editar ? 'Guardar' : 'Crear'; ?>
                            </button>

                            <?php if($asignatura_editar): ?>
                                <a href="cursos.php" class="btn btn-secondary"><i class="bi bi-x-lg"></i></a>
                            <?php endif; ?>
                        </form>

                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-modern table-hover mb-0 align-middle">
                                <tbody>
                                    <?php foreach($asignaturas as $a): ?>
                                        <tr class="<?php echo ($asignatura_editar && $asignatura_editar['id'] == $a['id']) ? 'table-active' : ''; ?>">
                                            <td class="fw-bold text-dark ps-4"><?php echo $a['nombre']; ?></td>
                                            <td class="text-end pe-4">
                                                <a href="cursos.php?editar_asignatura=<?php echo $a['id']; ?>" class="btn btn-sm btn-light text-primary border me-1">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>
                                                <a href="cursos.php?borrar_asignatura=<?php echo $a['id']; ?>" class="btn btn-sm btn-light text-danger border" onclick="return confirm('¿Eliminar esta asignatura?')">
                                                    <i class="bi bi-trash-fill"></i>
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

</body>
</html>