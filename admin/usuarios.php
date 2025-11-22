<?php
session_start();
require '../config/db.php';
require '../includes/funciones.php';
// 1. Seguridad
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'administrador') { 
    header("Location: ../login.php"); exit; 
}

$mensaje = "";
$tipo_msg = "";
$datos_editar = null;

// 2. CAPTURAR FILTRO (GET)
// Si hay un filtro en la URL (?filtro=alumno), lo guardamos.
$filtro_rol = isset($_GET['filtro']) ? $_GET['filtro'] : '';

// 3. LÓGICA EDICIÓN
if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    $stmtEdit = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmtEdit->execute([$id_editar]);
    $datos_editar = $stmtEdit->fetch();
}

// 4. LÓGICA BORRAR
if (isset($_GET['borrar'])) {
    $id_borrar = $_GET['borrar'];
    if ($id_borrar != $_SESSION['user_id']) {
        $stmtDel = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmtDel->execute([$id_borrar]);
        
        // Redirigir manteniendo el filtro
        $redirect = "usuarios.php" . ($filtro_rol ? "?filtro=$filtro_rol" : "");
        header("Location: $redirect"); exit;
    } else {
        $mensaje = "No puedes eliminar tu propia cuenta."; $tipo_msg = "danger";
    }
}

// 5. GUARDAR / ACTUALIZAR (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $rol = $_POST['rol'];
    $id_actualizar = $_POST['id_usuario'] ?? '';
    $pass_raw = $_POST['password']; 

    try {
        if (!empty($id_actualizar)) {
            // UPDATE
            if (!empty($pass_raw)) {
                $pass_hash = password_hash($pass_raw, PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET nombre=?, email=?, rol=?, password=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre, $email, $rol, $pass_hash, $id_actualizar]);
            } else {
                $sql = "UPDATE usuarios SET nombre=?, email=?, rol=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre, $email, $rol, $id_actualizar]);
            }
            $mensaje = "Usuario actualizado."; $tipo_msg = "info";
            $datos_editar = null;
        } else {
            // INSERT
            $pass_final = !empty($pass_raw) ? $pass_raw : '12345';
            $pass_hash = password_hash($pass_final, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (:n, :e, :p, :r)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['n'=>$nombre, 'e'=>$email, 'p'=>$pass_hash, 'r'=>$rol]);
            $mensaje = "Usuario creado."; $tipo_msg = "success";
        }
    } catch (PDOException $e) {
        $mensaje = "Error: Correo ya registrado."; $tipo_msg = "danger";
    }
}

// 6. LISTAR USUARIOS (CON FILTRO)
if (!empty($filtro_rol)) {
    // Si hay filtro, traemos solo ese rol
    $stmtList = $pdo->prepare("SELECT * FROM usuarios WHERE rol = ? ORDER BY id DESC");
    $stmtList->execute([$filtro_rol]);
    $usuarios = $stmtList->fetchAll();
} else {
    // Si no, traemos todos (Límite 50 para rapidez)
    $usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY id DESC LIMIT 50")->fetchAll();
}
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

<?php include '../includes/sidebar_admin.php'; ?>


    <div class="main-content">
        
        <h3 class="fw-bold mb-4"><i class="bi bi-people"></i> Directorio de Usuarios</h3>

        <?php if($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_msg; ?> shadow-sm border-0 mb-4"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <div class="row">
            
            <div class="col-lg-4 mb-4">
                <div class="card shadow border-0 sticky-top" style="top: 20px; z-index: 1;">
                    <div class="card-header <?php echo $datos_editar ? 'bg-warning' : 'bg-primary'; ?> text-white py-3">
                        <h5 class="mb-0 fw-bold">
                            <?php echo $datos_editar ? '✏️ Editar Usuario' : '➕ Nuevo Usuario'; ?>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="usuarios.php<?php echo $filtro_rol ? '?filtro='.$filtro_rol : ''; ?>">
                            <input type="hidden" name="id_usuario" value="<?php echo $datos_editar['id'] ?? ''; ?>">

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Nombre</label>
                                <input type="text" name="nombre" class="form-control" placeholder="Nombre Completo" 
                                       value="<?php echo $datos_editar['nombre'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Correo</label>
                                <input type="email" name="email" class="form-control" placeholder="correo@cole.cl" 
                                       value="<?php echo $datos_editar['email'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">
                                    <?php echo $datos_editar ? 'Nueva Clave (Opcional)' : 'Contraseña Inicial'; ?>
                                </label>
                                <input type="text" name="password" class="form-control" 
                                       placeholder="<?php echo $datos_editar ? 'Dejar vacía para mantener' : '12345'; ?>"
                                       <?php echo $datos_editar ? '' : 'value="12345"'; ?>>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">Rol</label>
                                <select name="rol" class="form-select" required>
                                    <option value="" disabled <?php echo !$datos_editar ? 'selected' : ''; ?>>Seleccionar...</option>
                                    <?php 
                                        $roles = ['alumno', 'profesor', 'director', 'administrador', 'apoderado'];
                                        foreach($roles as $r): 
                                            $selected = ($datos_editar && $datos_editar['rol'] == $r) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $r; ?>" <?php echo $selected; ?>><?php echo ucfirst($r); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button class="btn <?php echo $datos_editar ? 'btn-warning' : 'btn-primary'; ?> fw-bold">
                                    <?php echo $datos_editar ? 'GUARDAR CAMBIOS' : 'CREAR CUENTA'; ?>
                                </button>
                                <?php if($datos_editar): ?>
                                    <a href="usuarios.php<?php echo $filtro_rol ? '?filtro='.$filtro_rol : ''; ?>" class="btn btn-outline-secondary">Cancelar</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-secondary">Lista de Usuarios</h5>
                        
                        <form method="GET" class="d-flex align-items-center">
                            <label class="me-2 small fw-bold text-muted">Filtrar por:</label>
                            <select name="filtro" class="form-select form-select-sm w-auto border-primary" onchange="this.form.submit()">
                                <option value="">Todos los roles</option>
                                <option value="alumno" <?php echo ($filtro_rol=='alumno')?'selected':''; ?>>🎓 Alumnos</option>
                                <option value="profesor" <?php echo ($filtro_rol=='profesor')?'selected':''; ?>>👨‍🏫 Profesores</option>
                                <option value="apoderado" <?php echo ($filtro_rol=='apoderado')?'selected':''; ?>>👪 Apoderados</option>
                                <option value="director" <?php echo ($filtro_rol=='director')?'selected':''; ?>>👔 Directores</option>
                                <option value="administrador" <?php echo ($filtro_rol=='administrador')?'selected':''; ?>>⚙️ Admins</option>
                            </select>
                        </form>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-modern table-hover mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Usuario</th>
                                        <th>Rol</th>
                                        <th>Correo</th>
                                        <th class="text-end pe-4">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($usuarios) == 0): ?>
                                        <tr><td colspan="4" class="text-center py-4 text-muted">No se encontraron usuarios con ese filtro.</td></tr>
                                    <?php endif; ?>

                                    <?php foreach($usuarios as $u): ?>
                                        <tr class="<?php echo ($datos_editar && $datos_editar['id'] == $u['id']) ? 'table-warning' : ''; ?>">
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-light text-primary d-flex align-items-center justify-content-center fw-bold me-3" style="width: 35px; height: 35px; border: 1px solid #eee;">
                                                        <?php echo strtoupper(substr($u['nombre'], 0, 1)); ?>
                                                    </div>
                                                    <span class="fw-bold text-dark"><?php echo $u['nombre']; ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php 
                                                    $badge = match($u['rol']) {
                                                        'alumno' => 'bg-info text-dark',
                                                        'profesor' => 'bg-warning text-dark',
                                                        'administrador' => 'bg-dark text-white',
                                                        'director' => 'bg-danger text-white',
                                                        'apoderado' => 'bg-success text-white',
                                                        default => 'bg-secondary text-white'
                                                    };
                                                ?>
                                                <span class="badge <?php echo $badge; ?> rounded-pill px-3 py-2 text-uppercase" style="font-size: 0.7rem;">
                                                    <?php echo $u['rol']; ?>
                                                </span>
                                            </td>
                                            <td class="text-muted small"><?php echo $u['email']; ?></td>
                                            <td class="text-end pe-4">
                                                <a href="usuarios.php?editar=<?php echo $u['id']; ?><?php echo $filtro_rol ? '&filtro='.$filtro_rol : ''; ?>" 
                                                   class="btn btn-sm btn-light text-primary border me-1">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>

                                                <a href="usuarios.php?borrar=<?php echo $u['id']; ?><?php echo $filtro_rol ? '&filtro='.$filtro_rol : ''; ?>" 
                                                   class="btn btn-sm btn-light text-danger border" 
                                                   onclick="return confirm('¿Eliminar usuario?')">
                                                    <i class="bi bi-trash"></i>
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