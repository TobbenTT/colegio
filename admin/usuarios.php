<?php
session_start();
require '../config/db.php';

// Seguridad: Solo Admin
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'administrador') { 
    header("Location: ../login.php"); exit; 
}

$mensaje = "";
$tipo_msg = "";

// LÓGICA CREAR USUARIO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $pass_raw = $_POST['password']; 
    $rol = $_POST['rol'];

    // Encriptamos
    $pass_hash = password_hash($pass_raw, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (:n, :e, :p, :r)";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute(['n'=>$nombre, 'e'=>$email, 'p'=>$pass_hash, 'r'=>$rol]);
        $mensaje = "Usuario <strong>$nombre</strong> creado correctamente.";
        $tipo_msg = "success";
    } catch (PDOException $e) {
        $mensaje = "Error: El correo ya está registrado.";
        $tipo_msg = "danger";
    }
}

// LISTA DE ÚLTIMOS USUARIOS (Limitado a 20 para no saturar)
$usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY id DESC LIMIT 20")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Gestión de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

    <div class="sidebar">
        <div class="logo mb-4"><i class="bi bi-shield-lock-fill"></i> AdminPanel</div>
        <a href="dashboard.php"><i class="bi bi-speedometer2"></i> <span>Inicio</span></a>
        <hr class="text-secondary mx-3 my-2">
        <a href="usuarios.php" class="active"><i class="bi bi-people-fill"></i> <span>Usuarios</span></a>
        <a href="cursos.php"><i class="bi bi-building"></i> <span>Cursos y Materias</span></a>
        <a href="asignacion.php"><i class="bi bi-diagram-3-fill"></i> <span>Carga Académica</span></a>
        <div class="mt-5"><a href="../logout.php" class="text-danger"><i class="bi bi-box-arrow-left"></i> <span>Salir</span></a></div>
    </div>

    <div class="main-content">
        
        <h3 class="fw-bold mb-4"><i class="bi bi-people"></i> Directorio de Usuarios</h3>

        <?php if($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_msg; ?> shadow-sm border-0 mb-4"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <div class="row">
            
            <div class="col-lg-4 mb-4">
                <div class="card shadow border-0 sticky-top" style="top: 20px; z-index: 1;">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-person-plus"></i> Nuevo Usuario</h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Nombre Completo</label>
                                <input type="text" name="nombre" class="form-control" placeholder="Ej: Juan Pérez" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Correo Electrónico</label>
                                <input type="email" name="email" class="form-control" placeholder="juan@cole.cl" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Contraseña Inicial</label>
                                <input type="text" name="password" class="form-control" value="12345" required>
                                <div class="form-text text-xs">Se encriptará automáticamente.</div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">Rol del Usuario</label>
                                <select name="rol" class="form-select" required>
                                    <option value="" disabled selected>Seleccionar...</option>
                                    <option value="alumno">🎓 Alumno</option>
                                    <option value="profesor">👨‍🏫 Profesor</option>
                                    <option value="director">👔 Director</option>
                                    <option value="administrador">⚙️ Administrador</option>
                                    <option value="apoderado">👪 Apoderado</option>
                                </select>
                            </div>
                            <button class="btn btn-primary w-100 fw-bold py-2">CREAR CUENTA</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-secondary">Usuarios Recientes</h5>
                        <span class="badge bg-light text-dark border">Últimos 20</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-modern table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Usuario</th>
                                        <th>Rol</th>
                                        <th>Correo</th>
                                        <th class="text-end pe-4">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($usuarios as $u): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-light text-primary d-flex align-items-center justify-content-center fw-bold me-3" style="width: 40px; height: 40px; border: 1px solid #eee;">
                                                        <?php echo strtoupper(substr($u['nombre'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <span class="d-block fw-bold text-dark"><?php echo $u['nombre']; ?></span>
                                                        <small class="text-muted text-xs">ID: <?php echo $u['id']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php 
                                                    $badge = "bg-secondary";
                                                    if($u['rol']=='alumno') $badge="bg-info text-dark";
                                                    if($u['rol']=='profesor') $badge="bg-warning text-dark";
                                                    if($u['rol']=='administrador') $badge="bg-dark";
                                                ?>
                                                <span class="badge <?php echo $badge; ?> rounded-pill px-3 py-2 text-uppercase" style="font-size: 0.7rem;">
                                                    <?php echo $u['rol']; ?>
                                                </span>
                                            </td>
                                            <td class="text-muted small"><?php echo $u['email']; ?></td>
                                            <td class="text-end pe-4">
                                                <button class="btn btn-sm btn-light text-primary"><i class="bi bi-pencil-square"></i></button>
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