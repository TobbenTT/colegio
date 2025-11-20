<?php
session_start();
require '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'administrador') { header("Location: ../login.php"); exit; }

$mensaje = "";
$tipo_msg = "";

// 1. PROCESAR NUEVA ASIGNACIÓN (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['asignar'])) {
    $curso = $_POST['curso_id'];
    $materia = $_POST['asignatura_id'];
    $profe = $_POST['profesor_id'];

    // Validar duplicados
    $check = $pdo->prepare("SELECT id FROM programacion_academica WHERE curso_id=? AND asignatura_id=?");
    $check->execute([$curso, $materia]);
    
    if($check->fetch()) {
        $mensaje = "Este curso ya tiene profesor asignado para esa materia."; $tipo_msg = "warning";
    } else {
        $sql = "INSERT INTO programacion_academica (curso_id, asignatura_id, profesor_id) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if($stmt->execute([$curso, $materia, $profe])){
            $mensaje = "Asignación guardada exitosamente."; $tipo_msg = "success";
        }
    }
}

// 2. LÓGICA DE BORRADO
if (isset($_GET['borrar'])) {
    $id_borrar = $_GET['borrar'];
    $pdo->prepare("DELETE FROM programacion_academica WHERE id = ?")->execute([$id_borrar]);
    header("Location: asignacion.php"); exit; // Recargar para limpiar URL
}

// 3. OBTENER DATOS PARA SELECTS (Tanto para el modal como para los filtros)
$cursos = $pdo->query("SELECT * FROM cursos ORDER BY nombre")->fetchAll();
$asignaturas = $pdo->query("SELECT * FROM asignaturas ORDER BY nombre")->fetchAll();
$profesores = $pdo->query("SELECT * FROM usuarios WHERE rol = 'profesor' ORDER BY nombre")->fetchAll();

// 4. CONSTRUCCIÓN DE LA CONSULTA CON FILTROS (GET)
// Recibimos los filtros de la URL
$f_curso = $_GET['f_curso'] ?? '';
$f_materia = $_GET['f_materia'] ?? '';
$f_profe = $_GET['f_profe'] ?? '';

// Consulta Base
$sql = "SELECT pa.id, c.nombre as c, a.nombre as a, u.nombre as p, u.foto 
        FROM programacion_academica pa 
        JOIN cursos c ON pa.curso_id = c.id
        JOIN asignaturas a ON pa.asignatura_id = a.id
        JOIN usuarios u ON pa.profesor_id = u.id
        WHERE 1=1"; // Truco para agregar condiciones con AND

$params = [];

// Agregamos condiciones dinámicamente
if (!empty($f_curso)) {
    $sql .= " AND pa.curso_id = ?";
    $params[] = $f_curso;
}
if (!empty($f_materia)) {
    $sql .= " AND pa.asignatura_id = ?";
    $params[] = $f_materia;
}
if (!empty($f_profe)) {
    $sql .= " AND pa.profesor_id = ?";
    $params[] = $f_profe;
}

$sql .= " ORDER BY c.nombre, a.nombre"; // Orden final

$stmtList = $pdo->prepare($sql);
$stmtList->execute($params);
$lista = $stmtList->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Carga Académica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

    <?php include '../includes/sidebar_admin.php'; ?>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold"><i class="bi bi-diagram-3"></i> Distribución Académica</h3>
            <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAsignar">
                <i class="bi bi-plus-lg"></i> Nueva Asignación
            </button>
        </div>

        <?php if($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_msg; ?> shadow-sm border-0 mb-4"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 mb-4 bg-light">
            <div class="card-body py-3">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-auto fw-bold text-secondary"><i class="bi bi-funnel-fill"></i> Filtrar por:</div>
                    
                    <div class="col-md-3">
                        <select name="f_curso" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Todos los Cursos</option>
                            <?php foreach($cursos as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo ($f_curso == $c['id']) ? 'selected' : ''; ?>>
                                    <?php echo $c['nombre']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select name="f_materia" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Todas las Materias</option>
                            <?php foreach($asignaturas as $a): ?>
                                <option value="<?php echo $a['id']; ?>" <?php echo ($f_materia == $a['id']) ? 'selected' : ''; ?>>
                                    <?php echo $a['nombre']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select name="f_profe" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Todos los Profesores</option>
                            <?php foreach($profesores as $p): ?>
                                <option value="<?php echo $p['id']; ?>" <?php echo ($f_profe == $p['id']) ? 'selected' : ''; ?>>
                                    <?php echo $p['nombre']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-auto">
                        <?php if($f_curso || $f_materia || $f_profe): ?>
                            <a href="asignacion.php" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <?php if(count($lista) == 0): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-search display-4 opacity-25"></i>
                        <p class="mt-3">No se encontraron asignaciones con estos filtros.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-modern table-hover mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Curso</th>
                                    <th>Materia</th>
                                    <th>Profesor Encargado</th>
                                    <th class="text-end pe-4">Gestión</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($lista as $l): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <span class="badge bg-primary fs-6 rounded-pill shadow-sm"><?php echo $l['c']; ?></span>
                                        </td>
                                        <td class="fw-bold text-secondary"><?php echo $l['a']; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php 
                                                    // Mostrar avatar si tiene foto, si no iniciales
                                                    if ($l['foto'] && file_exists("../assets/uploads/perfiles/".$l['foto'])) {
                                                        echo '<img src="../assets/uploads/perfiles/'.$l['foto'].'" class="rounded-circle me-2 border" width="35" height="35">';
                                                    } else {
                                                        echo '<div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center fw-bold me-2" style="width: 35px; height: 35px;">'.strtoupper(substr($l['p'], 0, 1)).'</div>';
                                                    }
                                                ?>
                                                <span><?php echo $l['p']; ?></span>
                                            </div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="horarios.php?id=<?php echo $l['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill me-1">
                                                <i class="bi bi-calendar-week"></i> Horario
                                            </a>
                                            <a href="asignacion.php?borrar=<?php echo $l['id']; ?>" 
                                               class="btn btn-sm btn-light text-danger border-0"
                                               onclick="return confirm('¿Eliminar esta asignación? Se borrarán las clases y notas asociadas.')"
                                               title="Eliminar Asignación">
                                                <i class="bi bi-trash"></i>
                                            </a>
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

    <div class="modal fade" id="modalAsignar" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">Vincular Clase</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="fw-bold text-secondary small">Curso</label>
                            <select name="curso_id" class="form-select" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach($cursos as $c): echo "<option value='{$c['id']}'>{$c['nombre']}</option>"; endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold text-secondary small">Materia</label>
                            <select name="asignatura_id" class="form-select" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach($asignaturas as $a): echo "<option value='{$a['id']}'>{$a['nombre']}</option>"; endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="fw-bold text-secondary small">Profesor Titular</label>
                            <select name="profesor_id" class="form-select" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach($profesores as $p): echo "<option value='{$p['id']}'>{$p['nombre']}</option>"; endforeach; ?>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="asignar" class="btn btn-primary fw-bold">GUARDAR ASIGNACIÓN</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>