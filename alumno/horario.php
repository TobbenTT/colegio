<?php
session_start();
require '../config/db.php';
require '../includes/funciones.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'alumno') { header("Location: ../login.php"); exit; }

$alumno_id = $_SESSION['user_id'];

// 1. OBTENER CURSO
$stmtCurso = $pdo->prepare("SELECT curso_id FROM matriculas WHERE alumno_id = ?");
$stmtCurso->execute([$alumno_id]);
$curso = $stmtCurso->fetch();
if (!$curso) die("No tienes curso asignado.");
$curso_id = $curso['curso_id'];

// 2. OBTENER FECHAS DE ESTA SEMANA
$lunes_timestamp = strtotime('monday this week');
$fechas_semana = [];
$dias_espanol = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];

for ($i = 0; $i < 5; $i++) {
    $dia_nombre = $dias_espanol[$i];
    $fecha_dia = date('Y-m-d', strtotime("+$i days", $lunes_timestamp));
    $fechas_semana[$dia_nombre] = $fecha_dia;
}

// 3. BUSCAR PRUEBAS/TAREAS
$sqlPruebas = "SELECT a.fecha_limite, a.tipo, pa.id as prog_id
               FROM actividades a
               JOIN programacion_academica pa ON a.programacion_id = pa.id
               WHERE pa.curso_id = :cid 
               AND DATE(a.fecha_limite) BETWEEN :inicio AND :fin";

$stmtP = $pdo->prepare($sqlPruebas);
$stmtP->execute(['cid' => $curso_id, 'inicio' => reset($fechas_semana), 'fin' => end($fechas_semana)]);
$todos_eventos = $stmtP->fetchAll();

$calendario_eventos = [];
foreach($todos_eventos as $evt) {
    $fecha_solo = date('Y-m-d', strtotime($evt['fecha_limite']));
    $calendario_eventos[$fecha_solo][$evt['prog_id']] = $evt['tipo'];
}

// 4. CONSULTAR HORARIO (¡CON ESTADO!)
// Agregamos h.estado a la consulta
$sql = "SELECT h.dia, h.hora_inicio, h.hora_fin, h.estado, a.nombre as materia, u.nombre as profe, pa.id as prog_id
        FROM horarios h
        JOIN programacion_academica pa ON h.programacion_id = pa.id
        JOIN asignaturas a ON pa.asignatura_id = a.id
        JOIN usuarios u ON pa.profesor_id = u.id
        WHERE pa.curso_id = :cid
        ORDER BY FIELD(h.dia, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'), h.hora_inicio";

$stmt = $pdo->prepare($sql);
$stmt->execute(['cid' => $curso_id]);
$horario = $stmt->fetchAll(PDO::FETCH_GROUP); 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Horario Semanal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <style>
        .clase-card {
            cursor: pointer;
            transition: all 0.3s;
            border-left: 4px solid var(--accent-color);
            background: white;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            position: relative;
        }
        .clase-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-left-color: var(--primary-color);
        }
        .day-header {
            background: var(--primary-color);
            color: white;
            padding: 10px;
            border-radius: 10px 10px 0 0;
            text-align: center;
            font-weight: bold;
        }
        
        /* Badges Flotantes */
        .badge-prueba {
            position: absolute; top: -8px; right: -5px;
            background-color: #dc3545; color: white; font-size: 0.65rem;
            padding: 4px 8px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 10; animation: pulse 2s infinite;
        }
        .badge-tarea {
            position: absolute; top: -8px; right: -5px;
            background-color: #ffc107; color: #000; font-size: 0.65rem;
            padding: 4px 8px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 10;
        }
        .badge-suspendida {
            background-color: #dc3545; color: white; 
            font-size: 0.7rem; width: 100%; display: block;
            text-align: center; border-radius: 4px; margin-bottom: 5px;
        }

        @keyframes pulse {
            0% { transform: scale(1); } 50% { transform: scale(1.1); } 100% { transform: scale(1); }
        }
    </style>
</head>
<body>

    <?php include '../includes/sidebar_alumno.php'; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold"><i class="bi bi-calendar3"></i> Horario Semanal</h2>
            <span class="badge bg-light text-dark border">
                Semana del <?php echo date("d/m", $lunes_timestamp); ?>
            </span>
        </div>

        <div class="row flex-nowrap overflow-auto pb-3"> 
            <?php foreach ($dias_espanol as $dia): ?>
                <?php 
                    $fecha_actual = $fechas_semana[$dia];
                ?>
                <div class="col-md-4 col-lg-3 mb-4" style="min-width: 250px;">
                    <div class="card h-100 border-0 shadow-sm bg-transparent">
                        <div class="day-header shadow-sm">
                            <?php echo $dia; ?> <span class="fw-light small opacity-75"><?php echo date("d/m", strtotime($fecha_actual)); ?></span>
                        </div>
                        <div class="p-2">
                            <?php if (isset($horario[$dia])): ?>
                                <?php foreach ($horario[$dia] as $clase): ?>
                                    
                                    <?php 
                                        // 1. Detectar Evento (Prueba/Tarea)
                                        $tipo_evento = null;
                                        if (isset($calendario_eventos[$fecha_actual][$clase['prog_id']])) {
                                            $tipo_evento = $calendario_eventos[$fecha_actual][$clase['prog_id']];
                                        }

                                        // 2. Detectar Suspensión
                                        $clase_suspendida = ($clase['estado'] == 'suspendido');
                                        
                                        // Estilos Dinámicos
                                        $estilo_extra = $clase_suspendida ? 'border-left: 4px solid #dc3545 !important; background-color: #fff5f5;' : '';
                                        $texto_materia = $clase_suspendida ? 'text-danger text-decoration-line-through' : 'text-primary';
                                        
                                        // Acción al hacer clic (Alerta si está suspendida, Modal si es normal)
                                        $onclick = $clase_suspendida ? "alert('⛔ Esta clase ha sido suspendida por el profesor.')" : "verDetalle({$clase['prog_id']}, '{$clase['materia']}')";
                                    ?>

                                    <div class="clase-card p-3" style="<?php echo $estilo_extra; ?>" onclick="<?php echo $onclick; ?>">
                                        
                                        <?php if($clase_suspendida): ?>
                                            <div class="badge-suspendida">⛔ CLASE SUSPENDIDA</div>
                                        <?php endif; ?>

                                        <?php if(!$clase_suspendida): ?>
                                            <?php if($tipo_evento == 'prueba'): ?>
                                                <div class="badge-prueba"><i class="bi bi-exclamation-circle-fill"></i> EXAMEN</div>
                                            <?php elseif($tipo_evento == 'tarea'): ?>
                                                <div class="badge-tarea"><i class="bi bi-pencil-fill"></i> Entrega</div>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <h6 class="fw-bold mb-1 <?php echo $texto_materia; ?>"><?php echo $clase['materia']; ?></h6>
                                        
                                        <div class="d-flex justify-content-between small text-muted">
                                            <span><i class="bi bi-clock"></i> <?php echo substr($clase['hora_inicio'], 0, 5); ?></span>
                                            <span><i class="bi bi-person"></i> <?php echo explode(" ", $clase['profe'])[0]; ?></span>
                                        </div>
                                    </div>

                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center text-muted py-4 opacity-50">
                                    <i class="bi bi-brightness-high display-4"></i>
                                    <p class="small mt-2">Día Libre</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="modal fade" id="modalDetalle" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="tituloModal">Cargando...</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="contenidoModal">
                    <div class="text-center"><div class="spinner-border text-primary"></div></div>
                </div>
                <div class="modal-footer bg-light">
                    <a href="#" id="btnIrCurso" class="btn btn-primary w-100 rounded-pill fw-bold">Ir al Aula Virtual</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const myModal = new bootstrap.Modal(document.getElementById('modalDetalle'));
        
        function verDetalle(idProgramacion, nombreMateria) {
            document.getElementById('tituloModal').innerText = nombreMateria;
            document.getElementById('btnIrCurso').href = 'ver_curso.php?id=' + idProgramacion;
            document.getElementById('contenidoModal').innerHTML = '<div class="text-center"><div class="spinner-border text-primary"></div><p class="mt-2">Cargando datos...</p></div>';
            myModal.show();
            
            let formData = new FormData();
            formData.append('id', idProgramacion);
            
            fetch('ajax_detalle_materia.php', { method: 'POST', body: formData })
            .then(response => response.text())
            .then(html => { document.getElementById('contenidoModal').innerHTML = html; });
        }
    </script>
</body>
</html>