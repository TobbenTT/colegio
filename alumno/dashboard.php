<?php
session_start();
require '../config/db.php';

// 1. SEGURIDAD
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'alumno') { 
    header("Location: ../login.php"); exit; 
}

$alumno_id = $_SESSION['user_id'];

// 2. OBTENER CURSO
$sqlCurso = "SELECT c.id, c.nombre FROM matriculas m JOIN cursos c ON m.curso_id = c.id WHERE m.alumno_id = :id";
$stmt = $pdo->prepare($sqlCurso);
$stmt->execute(['id' => $alumno_id]);
$mi_curso = $stmt->fetch();

if (!$mi_curso) {
    $nombre_curso = "Sin Asignar";
    $curso_id = 0;
} else {
    $nombre_curso = $mi_curso['nombre'];
    $curso_id = $mi_curso['id'];
}

// 3. OBTENER MATERIAS
$lista_clases = [];
if ($curso_id > 0) {
    $sqlMaterias = "SELECT 
                        pa.id as id_unico_prog, 
                        a.nombre as nombre_materia, 
                        u.nombre as nombre_profe 
                    FROM programacion_academica pa
                    JOIN asignaturas a ON pa.asignatura_id = a.id
                    JOIN usuarios u ON pa.profesor_id = u.id
                    WHERE pa.curso_id = :cid";
    $stmtMat = $pdo->prepare($sqlMaterias);
    $stmtMat->execute(['cid' => $curso_id]);
    $lista_clases = $stmtMat->fetchAll();
}
$cantidad_materias = count($lista_clases);


// =========================================================
// 4. CÁLCULOS MATEMÁTICOS (ESTO ES LO QUE TE FALTABA)
// =========================================================

// A) CÁLCULO DE PROMEDIO
// AVG() es una funcion de SQL que calcula el promedio solo
$sqlProm = "SELECT AVG(nota) as promedio_final FROM entregas WHERE alumno_id = :id AND nota > 0";
$stmtProm = $pdo->prepare($sqlProm);
$stmtProm->execute(['id' => $alumno_id]);
$dato_promedio = $stmtProm->fetch();

// Si devuelve null (no hay notas), ponemos 0.0
$promedio_general = $dato_promedio['promedio_final'] ? number_format($dato_promedio['promedio_final'], 1) : '0.0';

// B) CÁLCULO DE ASISTENCIA
$sqlAsis = "SELECT 
                COUNT(*) as total_clases,
                SUM(CASE WHEN estado = 'presente' THEN 1 ELSE 0 END) as total_presentes
            FROM asistencia 
            WHERE alumno_id = :id";
$stmtAsis = $pdo->prepare($sqlAsis);
$stmtAsis->execute(['id' => $alumno_id]);
$dato_asis = $stmtAsis->fetch();

$porcentaje_asistencia = 100; // Valor por defecto (idealista)
if ($dato_asis && $dato_asis['total_clases'] > 0) {
    $porcentaje_asistencia = round(($dato_asis['total_presentes'] / $dato_asis['total_clases']) * 100);
}
// =========================================================

// Foto de perfil (opcional)
$avatar = isset($_SESSION['foto']) && !empty($_SESSION['foto']) ? "../assets/uploads/perfiles/".$_SESSION['foto'] : "https://cdn-icons-png.flaticon.com/512/2995/2995620.png";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

    <div class="sidebar">
        <div class="logo mb-4"><i class="bi bi-backpack2-fill"></i> Mi Colegio</div>
        <a href="dashboard.php" class="active"><i class="bi bi-grid-fill"></i> <span>Mis Clases</span></a>
        <a href="horario.php"><i class="bi bi-calendar-week"></i> <span>Horario</span></a>
        <a href="asistencia.php"><i class="bi bi-clipboard-check"></i> <span>Asistencia</span></a>
        <a href="mis_anotaciones.php"><i class="bi bi-exclamation-triangle"></i> <span>Hoja de Vida</span></a>
        <a href="perfil.php"><i class="bi bi-person-circle"></i> <span>Mi Perfil</span></a>
        <div class="mt-5">
            <a href="../logout.php" class="text-danger"><i class="bi bi-box-arrow-left"></i> <span>Salir</span></a>
        </div>
    </div>

    <div class="main-content">
        
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">¡Hola, <?php echo explode(" ", $_SESSION['nombre'])[0]; ?>! 🚀</h2>
            <p class="text-muted">Estudiante del <strong><?php echo $nombre_curso; ?></strong></p>
        </div>
        
        <?php 
            // LÓGICA PARA MOSTRAR LA FOTO REAL
            
            // 1. Definir ruta por defecto (Icono genérico)
            $avatar_url = "https://cdn-icons-png.flaticon.com/512/2995/2995620.png";
            
            // 2. Verificar si en la sesión hay una foto guardada
            if (isset($_SESSION['foto']) && !empty($_SESSION['foto'])) {
                $ruta_local = "../assets/uploads/perfiles/" . $_SESSION['foto'];
                
                // 3. Verificar si el archivo realmente existe en la carpeta
                if (file_exists($ruta_local)) {
                    $avatar_url = $ruta_local;
                }
            }
        ?>
        
        <img src="<?php echo $avatar_url; ?>" 
            width="50" height="50" 
            class="rounded-circle border border-2 border-white shadow-sm" 
            style="object-fit:cover;" 
            alt="Avatar">
    </div>
        <div class="row mb-5">
            
            <div class="col-md-4">
                <div class="card stat-card bg-gradient-primary h-100 p-3">
                    <div class="card-body">
                        <h5 class="card-title opacity-75">Mis Materias</h5>
                        <h2 class="display-4 fw-bold"><?php echo $cantidad_materias; ?></h2>
                        <i class="bi bi-book-half stat-icon"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card stat-card <?php echo ($porcentaje_asistencia < 85) ? 'bg-gradient-danger' : 'bg-gradient-warning'; ?> h-100 p-3">
                    <div class="card-body">
                        <h5 class="card-title opacity-75">Asistencia Gral.</h5>
                        <h2 class="display-4 fw-bold"><?php echo $porcentaje_asistencia; ?>%</h2> 
                        <i class="bi bi-graph-up-arrow stat-icon"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card stat-card <?php echo ($promedio_general < 4.0 && $promedio_general > 0) ? 'bg-danger' : 'bg-gradient-success'; ?> h-100 p-3">
                    <div class="card-body">
                        <h5 class="card-title opacity-75">Promedio</h5>
                        <h2 class="display-4 fw-bold"><?php echo $promedio_general; ?></h2>
                        <i class="bi bi-award-fill stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <h4 class="fw-bold mb-3 text-secondary">Tus Asignaturas</h4>
        <div class="row">
            <?php if($cantidad_materias == 0): ?>
                <div class="col-12">
                    <div class="alert alert-info">No tienes materias asignadas.</div>
                </div>
            <?php else: ?>
                <?php foreach($lista_clases as $clase): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card course-card-student h-100 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div class="icon-wrapper">
                                        <i class="bi bi-journal-bookmark-fill"></i>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm" type="button"><i class="bi bi-three-dots-vertical"></i></button>
                                    </div>
                                </div>
                                
                                <h5 class="fw-bold mt-2">
                                    <?php echo isset($clase['nombre_materia']) ? $clase['nombre_materia'] : 'Materia'; ?>
                                </h5>
                                <p class="text-muted small mb-4">
                                    <i class="bi bi-person"></i> 
                                    <?php echo isset($clase['nombre_profe']) ? $clase['nombre_profe'] : 'Profesor'; ?>
                                </p>
                                
                                <a href="ver_curso.php?id=<?php echo $clase['id_unico_prog']; ?>" class="btn btn-outline-primary w-100 rounded-pill fw-bold">
                                    Ir al Aula
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>