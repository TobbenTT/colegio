<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'director') { header("Location: ../login.php"); exit; }

// 1. OBTENER LISTA DE CURSOS (Para el filtro)
$cursos = $pdo->query("SELECT * FROM cursos ORDER BY nombre")->fetchAll();

// 2. VERIFICAR SI HAY FILTRO APLICADO
$curso_filtrado = $_GET['curso_id'] ?? '';

// 3. CONSTRUIR CONSULTA DE ALUMNOS
$sql = "SELECT 
            u.id, 
            u.nombre, 
            u.foto,
            c.nombre as curso,
            (SELECT AVG(nota) FROM entregas WHERE alumno_id = u.id AND nota > 0) as promedio,
            (SELECT COUNT(*) FROM asistencia WHERE alumno_id = u.id) as total_clases,
            (SELECT COUNT(*) FROM asistencia WHERE alumno_id = u.id AND estado='presente') as total_presente
        FROM usuarios u
        JOIN matriculas m ON u.id = m.alumno_id
        JOIN cursos c ON m.curso_id = c.id
        WHERE u.rol = 'alumno'";

// Si hay filtro, agregamos la condición
$params = [];
if (!empty($curso_filtrado)) {
    $sql .= " AND c.id = ?";
    $params[] = $curso_filtrado;
}

$sql .= " ORDER BY c.nombre, u.nombre";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data_cruda = $stmt->fetchAll(PDO::FETCH_ASSOC);

// VALIDACIÓN Y PROCESAMIENTO
$analisis = [];
$error_msg = "";

if (count($data_cruda) == 0) {
    if (!empty($curso_filtrado)) {
        $error_msg = "No se encontraron alumnos en el curso seleccionado.";
    } else {
        $error_msg = "No hay alumnos matriculados en el sistema.";
    }
} else {
    // Preparar datos para Python
    $lista_para_python = [];
    foreach($data_cruda as $row) {
        $pct_asis = ($row['total_clases'] > 0) ? round(($row['total_presente'] / $row['total_clases']) * 100) : 100;
        
        $lista_para_python[] = [
            'id' => $row['id'],
            'nombre' => $row['nombre'],
            'promedio' => $row['promedio'] ? number_format($row['promedio'], 1) : 0,
            'asistencia' => $pct_asis
        ];
    }

    // LLAMAR A PYTHON
    $json_input = base64_encode(json_encode($lista_para_python));
    
    // AJUSTA TU RUTA AQUÍ SI ES NECESARIO
    $path_script = "C:/xampp/htdocs/colegio/ia/prediccion_riesgo.py";
    $comando = "python $path_script $json_input";
    
    $salida = shell_exec($comando);
    $analisis = json_decode($salida, true);

    if (is_null($analisis)) {
        $error_msg = "Error: Python no respondió. Revisa la ruta del script.";
    } elseif (isset($analisis['error'])) {
        $error_msg = "IA Error: " . $analisis['error'];
        $analisis = [];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>IA - Análisis de Riesgo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

    <?php include '../includes/sidebar_director.php'; ?>


    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark"><i class="bi bi-cpu-fill text-primary"></i> Análisis Predictivo IA</h3>
                <p class="text-muted">Detección automática de riesgo escolar.</p>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4 bg-light">
            <div class="card-body py-3">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-auto fw-bold text-secondary">
                        <i class="bi bi-funnel-fill"></i> Filtrar Análisis:
                    </div>
                    <div class="col-md-4">
                        <select name="curso_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Todos los Cursos (Análisis General)</option>
                            <?php foreach($cursos as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo ($curso_filtrado == $c['id']) ? 'selected' : ''; ?>>
                                    <?php echo $c['nombre']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <?php if($curso_filtrado): ?>
                            <a href="riesgo_escolar.php" class="btn btn-outline-secondary">Limpiar</a>
                        <?php endif; ?>
                    </div>
                    <div class="col text-end">
                        <button type="button" class="btn btn-primary rounded-pill px-4" onclick="location.reload()">
                            <i class="bi bi-arrow-clockwise"></i> Recalcular IA
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if($error_msg): ?>
            <div class="alert alert-warning shadow-sm text-center py-5">
                <i class="bi bi-exclamation-circle display-4 mb-3 d-block opacity-50"></i>
                <?php echo $error_msg; ?>
            </div>
        <?php elseif(count($analisis) > 0): ?>

            <div class="row mb-4">
                <?php 
                    $criticos = count(array_filter($analisis, function($a){ return is_array($a) && $a['estado'] == 'CRÍTICO'; }));
                    $alertas = count(array_filter($analisis, function($a){ return is_array($a) && $a['estado'] == 'Alerta'; }));
                ?>
                
                <div class="col-md-6">
                    <div class="card bg-danger text-white shadow border-0">
                        <div class="card-body d-flex justify-content-between align-items-center p-4">
                            <div>
                                <h2 class="display-4 fw-bold mb-0"><?php echo $criticos; ?></h2>
                                <span>Riesgo Crítico (<?php echo empty($curso_filtrado) ? 'Total' : 'En este curso'; ?>)</span>
                            </div>
                            <i class="bi bi-lightning-charge-fill fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-warning text-dark shadow border-0">
                        <div class="card-body d-flex justify-content-between align-items-center p-4">
                            <div>
                                <h2 class="display-4 fw-bold mb-0"><?php echo $alertas; ?></h2>
                                <span>En Alerta</span>
                            </div>
                            <i class="bi bi-exclamation-triangle-fill fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-secondary">Detalle de Estudiantes</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-modern table-hover mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Estudiante</th>
                                    <th class="text-center">Promedio</th>
                                    <th class="text-center">Asistencia</th>
                                    <th class="text-center">Diagnóstico IA</th>
                                    <th>Recomendación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($analisis as $index => $resultado): ?>
                                    <?php 
                                        $data_orig = $data_cruda[$index] ?? ['curso' => 'N/A', 'foto' => 'default.jpg'];
                                        $foto = ($data_orig['foto'] && file_exists("../assets/uploads/perfiles/".$data_orig['foto'])) 
                                                ? "../assets/uploads/perfiles/".$data_orig['foto'] 
                                                : "https://cdn-icons-png.flaticon.com/512/2995/2995620.png";
                                    ?>
                                    
                                    <tr class="<?php echo ($resultado['estado']=='CRÍTICO') ? 'table-danger' : ''; ?>">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo $foto; ?>" width="40" height="40" class="rounded-circle me-3 border">
                                                <div>
                                                    <span class="fw-bold d-block"><?php echo $resultado['nombre']; ?></span>
                                                    <small class="text-muted"><?php echo $data_orig['curso']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center fw-bold"><?php echo $resultado['promedio']; ?></td>
                                        <td class="text-center"><?php echo $resultado['asistencia']; ?>%</td>
                                        <td class="text-center">
                                            <span class="badge bg-<?php echo $resultado['color']; ?> rounded-pill px-3">
                                                <?php echo $resultado['estado']; ?>
                                            </span>
                                            <small class="d-block text-muted mt-1" style="font-size: 0.7rem;">Score: <?php echo $resultado['score']; ?></small>
                                        </td>
                                        <td class="small text-secondary">
                                            <i class="bi bi-robot me-1"></i> <?php echo $resultado['mensaje_ia']; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>

</body>
</html>