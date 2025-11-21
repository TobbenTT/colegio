<?php
session_start();
require '../config/db.php';
require_once '../includes/funciones.php'; // Para notificaciones

// 1. Seguridad
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'alumno') { header("Location: ../login.php"); exit; }

$mi_id = $_SESSION['user_id'];
$mensaje_estado = "";

// 2. ENVIAR MENSAJE (Desde la bandeja)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enviar_mensaje'])) {
    $para = $_POST['destinatario_id'];
    $asunto = $_POST['asunto'];
    $texto = $_POST['mensaje'];
    
    if(!empty($para) && !empty($texto)){
        $stmt = $pdo->prepare("INSERT INTO mensajes (remitente_id, destinatario_id, asunto, mensaje) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$mi_id, $para, $asunto, $texto])) {
            
            // Notificar al destinatario
            $mi_nombre = $_SESSION['nombre'];
            enviarNotificacion($pdo, $para, "Mensaje de $mi_nombre: $asunto", "mensajes.php");
            
            $mensaje_estado = "Mensaje enviado correctamente.";
        }
    }
}

// 3. LEER MENSAJES RECIBIDOS
$sql = "SELECT m.*, u.nombre as remitente, u.rol as rol_remitente, u.foto
        FROM mensajes m 
        JOIN usuarios u ON m.remitente_id = u.id 
        WHERE m.destinatario_id = ? ORDER BY m.fecha DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$mi_id]);
$mis_mensajes = $stmt->fetchAll();

// 4. LISTA DE DESTINATARIOS (Solo Profes y Staff)
$sqlUsers = "SELECT id, nombre, rol FROM usuarios WHERE rol IN ('profesor', 'director', 'administrador') ORDER BY rol, nombre";
$destinatarios = $pdo->query($sqlUsers)->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Mensajes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .avatar-msg { width: 40px; height: 40px; object-fit: cover; border-radius: 50%; }
    </style>
</head>
<body>

    <?php include '../includes/sidebar_alumno.php'; ?>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold"><i class="bi bi-envelope-paper-fill"></i> Mis Mensajes</h2>
            <button class="btn btn-primary rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#modalRedactar">
                <i class="bi bi-pencil-fill"></i> Redactar
            </button>
        </div>

        <?php if($mensaje_estado): ?>
            <div class="alert alert-success shadow-sm border-0"><?php echo $mensaje_estado; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <?php if(count($mis_mensajes) == 0): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox display-1 opacity-25"></i>
                        <p class="mt-2">No tienes mensajes nuevos.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach($mis_mensajes as $msg): ?>
                            <div class="list-group-item p-4">
                                <div class="d-flex align-items-start">
                                    <?php $foto = $msg['foto'] ? "../assets/uploads/perfiles/".$msg['foto'] : "https://cdn-icons-png.flaticon.com/512/2995/2995620.png"; ?>
                                    <img src="<?php echo $foto; ?>" class="avatar-msg me-3 border">
                                    
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-0 fw-bold text-dark">
                                                <?php echo $msg['remitente']; ?> 
                                                <span class="badge bg-light text-secondary border ms-2"><?php echo ucfirst($msg['rol_remitente']); ?></span>
                                            </h6>
                                            <small class="text-muted"><?php echo date("d/m H:i", strtotime($msg['fecha'])); ?></small>
                                        </div>
                                        <p class="mb-1 fw-bold text-primary mt-1"><?php echo $msg['asunto']; ?></p>
                                        <p class="mb-0 text-secondary"><?php echo nl2br($msg['mensaje']); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
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
                            <label class="form-label fw-bold small text-muted">Para:</label>
                            <select name="destinatario_id" class="form-select" required>
                                <option value="">Seleccionar Profesor/Directivo...</option>
                                <?php foreach($destinatarios as $user): ?>
                                    <option value="<?php echo $user['id']; ?>">
                                        <?php echo $user['nombre']; ?> (<?php echo ucfirst($user['rol']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Asunto:</label>
                            <input type="text" name="asunto" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Mensaje:</label>
                            <textarea name="mensaje" class="form-control" rows="4" required></textarea>
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