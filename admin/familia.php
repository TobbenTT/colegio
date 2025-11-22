<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'administrador') { header("Location: ../login.php"); exit; }

$mensaje = "";
$tipo_msg = "";

// 1. CAPTURAR EL FILTRO DE CURSO (GET)
$filtro_curso = $_GET['filtro_curso'] ?? '';

// 2. VINCULAR (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['vincular'])) {
    $apoderado_id = $_POST['apoderado_id'];
    $alumno_id = $_POST['alumno_id'];

    // Validar que no exista el vínculo
    $check = $pdo->prepare("SELECT id FROM familia WHERE apoderado_id=? AND alumno_id=?");
    $check->execute([$apoderado_id, $alumno_id]);
    
    if($check->fetch()) {
        $mensaje = "Este vínculo ya existe."; $tipo_msg = "warning";
    } else {
        $sql = "INSERT INTO familia (apoderado_id, alumno_id) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        if($stmt->execute([$apoderado_id, $alumno_id])){
            $mensaje = "Vinculación exitosa."; $tipo_msg = "success";
        }
    }
}

// 3. BORRAR
if (isset($_GET['borrar'])) {
    $pdo->prepare("DELETE FROM familia WHERE id = ?")->execute([$_GET['borrar']]);
    // Mantenemos el filtro al recargar
    $redireccion = "familia.php" . ($filtro_curso ? "?filtro_curso=$filtro_curso" : "");
    header("Location: $redireccion"); exit;
}

// 4. CONSULTAS PARA LOS SELECTS
// A) Lista de Cursos (Para el filtro)
$cursos = $pdo->query("SELECT * FROM cursos ORDER BY nombre")->fetchAll();

// B) Lista de Apoderados (Todos)
$apoderados = $pdo->query("SELECT id, nombre, email FROM usuarios WHERE rol = 'apoderado' ORDER BY nombre")->fetchAll();

// C) Lista de Alumnos (¡FILTRADA!)
if (!empty($filtro_curso)) {
    // Si eligió un curso, traemos SOLO los alumnos matriculados en ese curso
    $sqlAlu = "SELECT u.id, u.nombre 
               FROM usuarios u
               JOIN matriculas m ON u.id = m.alumno_id
               WHERE u.rol = 'alumno' AND m.curso_id = ?
               ORDER BY u.nombre";
    $stmtAlu = $pdo->prepare($sqlAlu);
    $stmtAlu->execute([$filtro_curso]);
    $alumnos = $stmtAlu->fetchAll();
} else {
    // Si no hay filtro, traemos todos (o podrías dejarlo vacío para obligar a filtrar)
    $alumnos = $pdo->query("SELECT id, nombre FROM usuarios WHERE rol = 'alumno' ORDER BY nombre")->fetchAll();
}

// 5. LISTA DE FAMILIAS (TABLA DERECHA)
// También podríamos filtrarla si quieres, pero dejémosla completa para ver el panorama general
$familias = $pdo->query("SELECT f.id, papa.nombre as apoderado, hijo.nombre as alumno, hijo.foto, c.nombre as curso
                         FROM familia f 
                         JOIN usuarios papa ON f.apoderado_id = papa.id 
                         JOIN usuarios hijo ON f.alumno_id = hijo.id 
                         LEFT JOIN matriculas m ON hijo.id = m.alumno_id
                         LEFT JOIN cursos c ON m.curso_id = c.id
                         ORDER BY papa.nombre")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Gestión Familiar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

    <?php include '../includes/sidebar_admin.php'; ?>

    <div class="main-content">
        
        <h3 class="fw-bold mb-4"><i class="bi bi-people-fill"></i> Vinculación Familiar</h3>

        <?php if($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_msg; ?> shadow-sm border-0 mb-4"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <div class="row">
            
            <div class="col-lg-4 mb-4">
                <div class="card shadow border-0 sticky-top" style="top: 20px; z-index: 1;">
                    <div class="card-header bg-info text-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-link-45deg"></i> Nuevo Vínculo</h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary small">1. Apoderado</label>
                                <select name="apoderado_id" class="form-select" required>
                                    <option value="">Buscar apoderado...</option>
                                    <?php foreach($apoderados as $apo): ?>
                                        <option value="<?php echo $apo['id']; ?>">
                                            <?php echo $apo['nombre']; ?> (<?php echo $apo['email']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <hr class="my-4">

                            <div class="mb-2">
                                <label class="form-label fw-bold text-primary small"><i class="bi bi-filter"></i> Filtrar Alumnos por Curso</label>
                                <select class="form-select border-primary" onchange="window.location.href='familia.php?filtro_curso='+this.value">
                                    <option value="">Mostrar Todos los Alumnos</option>
                                    <?php foreach($cursos as $c): ?>
                                        <option value="<?php echo $c['id']; ?>" <?php echo ($filtro_curso == $c['id']) ? 'selected' : ''; ?>>
                                            <?php echo $c['nombre']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-secondary small">2. Seleccionar Alumno (Hijo/a)</label>
                                <select name="alumno_id" class="form-select" required>
                                    <option value="">
                                        <?php echo empty($filtro_curso) ? 'Selecciona un alumno...' : '-- Alumnos del curso seleccionado --'; ?>
                                    </option>
                                    
                                    <?php foreach($alumnos as $alu): ?>
                                        <option value="<?php echo $alu['id']; ?>"><?php echo $alu['nombre']; ?></option>
                                    <?php endforeach; ?>
                                    
                                    <?php if(count($alumnos) == 0 && !empty($filtro_curso)): ?>
                                        <option disabled>No hay alumnos en este curso</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="vincular" class="btn btn-info text-white fw-bold py-2">
                                    CREAR RELACIÓN
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-secondary">Familias Registradas</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-modern table-hover mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Apoderado</th>
                                        <th>Pupilo</th>
                                        <th>Curso</th>
                                        <th class="text-end pe-4">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($familias) == 0): ?>
                                        <tr><td colspan="4" class="text-center py-4 text-muted">No hay vínculos creados.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($familias as $f): ?>
                                            <tr>
                                                <td class="ps-4 fw-bold text-dark">
                                                    <div class="d-flex align-items-center">
                                                        <div class="rounded-circle bg-light d-flex justify-content-center align-items-center me-2 text-secondary" style="width:35px;height:35px;">
                                                            <i class="bi bi-person-fill"></i>
                                                        </div>
                                                        <?php echo $f['apoderado']; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php $foto = $f['foto'] ? "../assets/uploads/perfiles/".$f['foto'] : "https://cdn-icons-png.flaticon.com/512/2995/2995620.png"; ?>
                                                        <img src="<?php echo $foto; ?>" width="30" height="30" class="rounded-circle me-2 border">
                                                        <span class="text-primary fw-bold"><?php echo $f['alumno']; ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if($f['curso']): ?>
                                                        <span class="badge bg-warning text-dark rounded-pill"><?php echo $f['curso']; ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary rounded-pill">Sin Curso</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <a href="familia.php?borrar=<?php echo $f['id']; ?><?php echo $filtro_curso ? '&filtro_curso='.$filtro_curso : ''; ?>" 
                                                       class="btn btn-sm btn-light text-danger border" 
                                                       onclick="return confirm('¿Desvincular?')"
                                                       title="Eliminar vínculo">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
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