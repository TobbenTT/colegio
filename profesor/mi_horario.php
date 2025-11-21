<?php
session_start();
require '../config/db.php';

// 1. Seguridad
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'profesor') { header("Location: ../login.php"); exit; }

$profesor_id = $_SESSION['user_id'];
$mensaje = "";

// 2. LÓGICA: CAMBIAR ESTADO (SUSPENDER/ACTIVAR)
if (isset($_GET['toggle_id'])) {
    $id_bloque = $_GET['toggle_id'];
    $estado_actual = $_GET['estado'];
    
    // Calculamos el nuevo estado
    $nuevo_estado = ($estado_actual == 'activo') ? 'suspendido' : 'activo';

    // Seguridad extra: Verificar que este horario pertenezca a una materia de ESTE profesor
    // (Para que no pueda suspender clases de otros)
    $check = $pdo->prepare("SELECT h.id FROM horarios h 
                            JOIN programacion_academica pa ON h.programacion_id = pa.id 
                            WHERE h.id = ? AND pa.profesor_id = ?");
    $check->execute([$id_bloque, $profesor_id]);
    
    if ($check->fetch()) {
        $stmt = $pdo->prepare("UPDATE horarios SET estado = ? WHERE id = ?");
        $stmt->execute([$nuevo_estado, $id_bloque]);
        $mensaje = "El estado de la clase ha sido cambiado a: " . strtoupper($nuevo_estado);
    }
}

// 3. OBTENER HORARIO DEL PROFESOR
// Traemos día, hora, curso, materia y ESTADO
$sql = "SELECT h.*, c.nombre as curso, a.nombre as materia 
        FROM horarios h
        JOIN programacion_academica pa ON h.programacion_id = pa.id
        JOIN cursos c ON pa.curso_id = c.id
        JOIN asignaturas a ON pa.asignatura_id = a.id
        WHERE pa.profesor_id = :pid
        ORDER BY FIELD(h.dia, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'), h.hora_inicio";

$stmt = $pdo->prepare($sql);
$stmt->execute(['pid' => $profesor_id]);
$mi_horario = $stmt->fetchAll(PDO::FETCH_GROUP); // Agrupar por día
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Mi Horario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

    <?php include '../includes/sidebar_profesor.php'; ?>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold"><i class="bi bi-calendar-range"></i> Gestionar Mis Clases</h3>
            <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill">Volver</a>
        </div>

        <?php if($mensaje): ?>
            <div class="alert alert-info shadow-sm border-0 mb-4">
                <i class="bi bi-info-circle-fill me-2"></i> <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php 
            $dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
            foreach ($dias_semana as $dia): 
            ?>
                <div class="col-md-6 col-xl-4 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold text-primary"><?php echo $dia; ?></h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (isset($mi_horario[$dia])): ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($mi_horario[$dia] as $clase): ?>
                                        
                                        <?php 
                                            $suspendida = ($clase['estado'] == 'suspendido');
                                            $bg_item = $suspendida ? 'bg-danger-subtle' : '';
                                            $txt_color = $suspendida ? 'text-danger' : 'text-dark';
                                        ?>

                                        <li class="list-group-item p-3 <?php echo $bg_item; ?>">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <small class="text-muted fw-bold">
                                                        <?php echo substr($clase['hora_inicio'], 0, 5) . " - " . substr($clase['hora_fin'], 0, 5); ?>
                                                    </small>
                                                    <h6 class="mb-0 fw-bold mt-1 <?php echo $txt_color; ?>">
                                                        <?php echo $clase['materia']; ?> 
                                                        <span class="badge bg-dark ms-1"><?php echo $clase['curso']; ?></span>
                                                    </h6>
                                                    <?php if($suspendida): ?>
                                                        <span class="badge bg-danger mt-2">⛔ SUSPENDIDA</span>
                                                    <?php endif; ?>
                                                </div>

                                                <div>
                                                    <a href="mi_horario.php?toggle_id=<?php echo $clase['id']; ?>&estado=<?php echo $clase['estado']; ?>" 
                                                       class="btn btn-sm <?php echo $suspendida ? 'btn-success' : 'btn-outline-danger'; ?> rounded-pill fw-bold"
                                                       title="<?php echo $suspendida ? 'Reactivar Clase' : 'Suspender Clase'; ?>">
                                                        <?php echo $suspendida ? '<i class="bi bi-check-lg"></i> Activar' : '<i class="bi bi-x-lg"></i> Suspender'; ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <div class="p-4 text-center text-muted small">Sin clases.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>