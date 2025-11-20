<?php
session_start();
require '../config/db.php';
require_once '../includes/funciones.php'; // IMPORTANTE

// 1. Seguridad
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'profesor') { header("Location: ../login.php"); exit; }

$mi_id = $_SESSION['user_id'];
$mensaje_estado = "";

// 2. ENVIAR MENSAJE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enviar_mensaje'])) {
    $para = $_POST['destinatario_id'];
    $asunto = $_POST['asunto'];
    $texto = $_POST['mensaje'];
    
    if(!empty($para) && !empty($texto)){
        $stmt = $pdo->prepare("INSERT INTO mensajes (remitente_id, destinatario_id, asunto, mensaje) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$mi_id, $para, $asunto, $texto])) {
            
            // --- LÓGICA DE NOTIFICACIÓN ---
            $mi_nombre = $_SESSION['nombre'];
            $msg_notif = "Nuevo mensaje de $mi_nombre: $asunto";
            
            // Determinar a dónde mandarlo (si es alumno, a su carpeta, si es admin, a la suya)
            // Por simplicidad, como la ruta es relativa desde donde está el usuario, 
            // guardamos 'mensajes.php' y el sistema de notificaciones lo ajusta.
            enviarNotificacion($pdo, $para, $msg_notif, 'mensajes.php');
            // ------------------------------

            $mensaje_estado = "Mensaje enviado correctamente.";
        }
    }
}

// 3. LEER MENSAJES
$sql = "SELECT m.*, u.nombre as remitente, u.rol as rol_remitente 
        FROM mensajes m 
        JOIN usuarios u ON m.remitente_id = u.id 
        WHERE m.destinatario_id = ? ORDER BY m.fecha DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$mi_id]);
$mis_mensajes = $stmt->fetchAll();

// 4. LISTA USUARIOS
$sqlUsers = "SELECT id, nombre, rol FROM usuarios WHERE id != ? ORDER BY rol, nombre";
$stmtUsers = $pdo->prepare($sqlUsers);
$stmtUsers->execute([$mi_id]);
$destinatarios = $stmtUsers->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mensajería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .avatar-circle { width: 40px; height: 40px; background-color: var(--accent-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }
    </style>
</head>
<body>

    <?php include '../includes/sidebar_profesor.php'; ?>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold"><i class="bi bi-envelope"></i> Buzón</h2>
            <button class="btn btn-primary rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#modalRedactar">
                <i class="bi bi-pencil-square"></i> Redactar Nuevo
            </button>
        </div>

        <?php if($mensaje_estado): ?>
            <div class="alert alert-success shadow-sm border-0"><?php echo $mensaje_estado; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3"><h5 class="mb-0 fw-bold text-secondary">Bandeja de Entrada</h5></div>
            <div class="card-body p-0">
                <?php if(count($mis_mensajes) == 0): ?>
                    <div class="text-center py-5 text-muted"><i class="bi bi-inbox display-1 opacity-25"></i><p class="mt-2">No tienes mensajes nuevos.</p></div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach($mis_mensajes as $msg): ?>
                            <li class="list-group-item p-3 hover-effect">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-3 flex-shrink-0"><?php echo strtoupper(substr($msg['remitente'], 0, 1)); ?></div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-0 text-dark"><?php echo $msg['remitente']; ?> <span class="badge bg-light text-secondary border fw-normal ms-2"><?php echo ucfirst($msg['rol_remitente']); ?></span></h6>
                                            <small class="text-muted"><?php echo date("d M H:i", strtotime($msg['fecha'])); ?></small>
                                        </div>
                                        <p class="mb-1 fw-bold text-primary mt-1"><?php echo $msg['asunto']; ?></p>
                                        <p class="mb-0 text-muted small text-truncate"><?php echo $msg['mensaje']; ?></p>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <div class="modal fade" id="modalRedactar" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">Nuevo Mensaje</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Destinatario</label>
                            <select name="destinatario_id" class="form-select" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach($destinatarios as $user): ?>
                                    <option value="<?php echo $user['id']; ?>"><?php echo $user['nombre']; ?> (<?php echo ucfirst($user['rol']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Asunto</label>
                            <input type="text" name="asunto" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Mensaje</label>
                            <textarea name="mensaje" class="form-control" rows="5" required></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="enviar_mensaje" class="btn btn-primary fw-bold">ENVIAR</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>