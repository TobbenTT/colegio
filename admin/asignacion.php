<?php
session_start();
require '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'administrador') { header("Location: ../login.php"); exit; }

$mensaje = "";
$tipo_msg = "";

// 1. ASIGNAR (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['asignar'])) {
    $curso = $_POST['curso_id'];
    $materia = $_POST['asignatura_id'];
    $profe = $_POST['profesor_id'];

    // Validar duplicados
    $check = $pdo->prepare("SELECT id FROM programacion_academica WHERE curso_id=? AND asignatura_id=?");
    $check->execute([$curso, $materia]);
    
    if($check->fetch()) {
        $mensaje = "Este curso ya tiene profesor para esa materia."; $tipo_msg = "warning";
    } else {
        $sql = "INSERT INTO programacion_academica (curso_id, asignatura_id, profesor_id) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if($stmt->execute([$curso, $materia, $profe])){
            $mensaje = "Asignación guardada exitosamente."; $tipo_msg = "success";
        }
    }
}

// Listas para selects
$cursos = $pdo->query("SELECT * FROM cursos ORDER BY nombre")->fetchAll();
$asignaturas = $pdo->query("SELECT * FROM asignaturas ORDER BY nombre")->fetchAll();
$profesores = $pdo->query("SELECT * FROM usuarios WHERE rol = 'profesor' ORDER BY nombre")->fetchAll();

// Lista Principal
$lista = $pdo->query("SELECT pa.id, c.nombre as c, a.nombre as a, u.nombre as p, u.foto 
                      FROM programacion_academica pa 
                      JOIN cursos c ON pa.curso_id = c.id
                      JOIN asignaturas a ON pa.asignatura_id = a.id
                      JOIN usuarios u ON pa.profesor_id = u.id
                      ORDER BY c.nombre, a.nombre")->fetchAll();
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

    <div class="sidebar">
        <div class="logo mb-4"><i class="bi bi-shield-lock-fill"></i> AdminPanel</div>
        <a href="dashboard.php"><i class="bi bi-speedometer2"></i> <span>Inicio</span></a>
        <hr class="text-secondary mx-3 my-2">
        <a href="usuarios.php"><i class="bi bi-people-fill"></i> <span>Usuarios</span></a>
        <a href="cursos.php"><i class="bi bi-building"></i> <span>Cursos y Materias</span></a>
        <a href="asignacion.php" class="active"><i class="bi bi-diagram-3-fill"></i> <span>Carga Académica</span></a>
        <div class="mt-5"><a href="../logout.php" class="text-danger"><i class="bi bi-box-arrow-left"></i> <span>Salir</span></a></div>
    </div>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold"><i class="bi bi-diagram-3"></i> Distribución Académica</h3>
            <button class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#modalAsignar">
                <i class="bi bi-plus-lg"></i> Nueva Asignación
            </button>
        </div>

        <?php if($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_msg; ?> shadow-sm border-0 mb-4"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
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
                                        <span class="badge bg-primary fs-6 rounded-pill"><?php echo $l['c']; ?></span>
                                    </td>
                                    <td class="fw-bold text-secondary"><?php echo $l['a']; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center fw-bold me-2" style="width: 35px; height: 35px;">
                                                <?php echo strtoupper(substr($l['p'], 0, 1)); ?>
                                            </div>
                                            <span><?php echo $l['p']; ?></span>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="horarios.php?id=<?php echo $l['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                                            <i class="bi bi-calendar-week"></i> Horario
                                        </a>
                                        <button class="btn btn-sm btn-light text-danger border-0 ms-1"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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