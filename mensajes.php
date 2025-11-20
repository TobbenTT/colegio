<?php
session_start();
require 'config/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$mi_id = $_SESSION['user_id'];
$mensaje_estado = "";

// ENVIAR MENSAJE
if (isset($_POST['enviar'])) {
    $para = $_POST['destinatario'];
    $asunto = $_POST['asunto'];
    $texto = $_POST['mensaje'];
    
    $stmt = $pdo->prepare("INSERT INTO mensajes (remitente_id, destinatario_id, asunto, mensaje) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$mi_id, $para, $asunto, $texto])) {
        $mensaje_estado = "Mensaje enviado.";
    }
}

// LEER MENSAJES RECIBIDOS
$sql = "SELECT m.*, u.nombre as remitente FROM mensajes m JOIN usuarios u ON m.remitente_id = u.id WHERE m.destinatario_id = ? ORDER BY m.fecha DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$mi_id]);
$mis_mensajes = $stmt->fetchAll();

// LISTA DE USUARIOS PARA EL SELECT (Simple: muestra a todos. En producción podrías filtrar)
$usuarios = $pdo->query("SELECT id, nombre, rol FROM usuarios WHERE id != $mi_id ORDER BY nombre")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Mensajería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h3>Mensajería Interna</h3>
        <a href="javascript:history.back()" class="btn btn-secondary mb-3">Volver</a>
        
        <?php if($mensaje_estado): ?><div class="alert alert-success"><?php echo $mensaje_estado; ?></div><?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">Redactar</div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-2">
                                <label>Para:</label>
                                <select name="destinatario" class="form-select" required>
                                    <option value="">Seleccionar...</option>
                                    <?php foreach($usuarios as $u): ?>
                                        <option value="<?php echo $u['id']; ?>"><?php echo $u['nombre'] . " (" . ucfirst($u['rol']) . ")"; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-2"><input type="text" name="asunto" class="form-control" placeholder="Asunto" required></div>
                            <div class="mb-2"><textarea name="mensaje" class="form-control" rows="4" placeholder="Escribe aquí..." required></textarea></div>
                            <button type="submit" name="enviar" class="btn btn-primary w-100">Enviar</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Bandeja de Entrada</div>
                    <div class="list-group list-group-flush">
                        <?php foreach($mis_mensajes as $msg): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo $msg['asunto']; ?></h6>
                                    <small><?php echo date("d/m H:i", strtotime($msg['fecha'])); ?></small>
                                </div>
                                <p class="mb-1 text-muted small">De: <?php echo $msg['remitente']; ?></p>
                                <p class="mb-1"><?php echo $msg['mensaje']; ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>