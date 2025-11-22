<?php
session_start();
require '../config/db.php';
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['rol'], ['administrador', 'director'])) { 
    header("Location: ../login.php"); exit; 
}

$mensaje = "";

// 1. CREAR EVENTO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_evento'])) {
    $titulo = $_POST['titulo'];
    $inicio = $_POST['fecha'] . ' ' . $_POST['hora_inicio'];
    $fin = $_POST['fecha'] . ' ' . $_POST['hora_fin'];
    $color = $_POST['color'];
    $desc = $_POST['descripcion'];

    $sql = "INSERT INTO eventos (titulo, fecha_inicio, fecha_fin, color, descripcion, creado_por) VALUES (?, ?, ?, ?, ?, ?)";
    if ($pdo->prepare($sql)->execute([$titulo, $inicio, $fin, $color, $desc, $_SESSION['user_id']])) {
        $mensaje = "Evento agendado.";
    }
}

// 2. BORRAR EVENTO
if (isset($_GET['borrar'])) {
    $pdo->prepare("DELETE FROM eventos WHERE id = ?")->execute([$_GET['borrar']]);
    header("Location: calendario_gestion.php"); exit;
}

// Lista para tabla
$lista = $pdo->query("SELECT * FROM eventos ORDER BY fecha_inicio DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Gestión Calendario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

    <?php 
    // Incluir sidebar según rol
    if($_SESSION['rol']=='administrador') include '../includes/sidebar_admin.php';
    else include '../includes/sidebar_director.php'; 
    ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold"><i class="bi bi-calendar-check"></i> Calendario Académico</h3>
            <a href="ver_calendario.php" class="btn btn-outline-primary rounded-pill">Ver Vista Calendario</a>
        </div>

        <?php if($mensaje): ?><div class="alert alert-success"><?php echo $mensaje; ?></div><?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow border-0">
                    <div class="card-header bg-dark text-white py-3"><h5 class="mb-0">Nuevo Evento</h5></div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Título</label>
                                <input type="text" name="titulo" class="form-control" required placeholder="Ej: Día del Alumno">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Fecha</label>
                                <input type="date" name="fecha" class="form-control" required>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <label class="small">Inicio</label>
                                    <input type="time" name="hora_inicio" class="form-control" value="08:00" required>
                                </div>
                                <div class="col">
                                    <label class="small">Fin</label>
                                    <input type="time" name="hora_fin" class="form-control" value="13:00" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Color / Tipo</label>
                                <select name="color" class="form-select">
                                    <option value="#3788d8" style="color:#3788d8">🔵 General (Azul)</option>
                                    <option value="#e74c3c" style="color:#e74c3c">🔴 Feriado / Suspensión (Rojo)</option>
                                    <option value="#27ae60" style="color:#27ae60">🟢 Actividad / Celebración (Verde)</option>
                                    <option value="#f39c12" style="color:#f39c12">🟠 Reunión / Admin (Naranja)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Detalle</label>
                                <textarea name="descripcion" class="form-control" rows="2"></textarea>
                            </div>
                            <button type="submit" name="crear_evento" class="btn btn-primary w-100 fw-bold">AGENDAR</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow border-0">
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light"><tr><th class="ps-4">Evento</th><th>Fecha</th><th>Acción</th></tr></thead>
                            <tbody>
                                <?php foreach($lista as $ev): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <span class="badge me-2" style="background-color: <?php echo $ev['color']; ?>">&nbsp;</span>
                                            <strong><?php echo $ev['titulo']; ?></strong>
                                        </td>
                                        <td><?php echo date("d/m/Y H:i", strtotime($ev['fecha_inicio'])); ?></td>
                                        <td><a href="?borrar=<?php echo $ev['id']; ?>" class="btn btn-sm btn-light text-danger" onclick="return confirm('¿Borrar?')"><i class="bi bi-trash"></i></a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>