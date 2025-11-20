<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['id'])) {
    echo "Error de acceso"; exit;
}

$alumno_id = $_SESSION['user_id'];
$prog_id = $_POST['id']; // ID de la programación (La materia específica)

// 1. CALCULAR ASISTENCIA DE ESTA MATERIA
$sqlAsis = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN estado = 'presente' THEN 1 ELSE 0 END) as presentes
    FROM asistencia 
    WHERE alumno_id = ? AND programacion_id = ?";
$stmtAsis = $pdo->prepare($sqlAsis);
$stmtAsis->execute([$alumno_id, $prog_id]);
$datos_asis = $stmtAsis->fetch();

$porcentaje = ($datos_asis['total'] > 0) 
    ? round(($datos_asis['presentes'] / $datos_asis['total']) * 100) 
    : 100;

$color_barra = ($porcentaje < 85) ? 'bg-danger' : 'bg-success';

// 2. BUSCAR NOTAS DE ESTA MATERIA
// Unimos actividades con entregas para saber la nota de cada tarea
$sqlNotas = "SELECT a.titulo, e.nota 
             FROM actividades a
             LEFT JOIN entregas e ON a.id = e.actividad_id AND e.alumno_id = ?
             WHERE a.programacion_id = ? AND a.tipo = 'tarea'
             ORDER BY a.created_at DESC";
$stmtNotas = $pdo->prepare($sqlNotas);
$stmtNotas->execute([$alumno_id, $prog_id]);
$notas = $stmtNotas->fetchAll();

// 3. CALCULAR PROMEDIO (Opcional)
$suma_notas = 0;
$cant_notas = 0;
foreach($notas as $n) {
    if($n['nota'] > 0) {
        $suma_notas += $n['nota'];
        $cant_notas++;
    }
}
$promedio = ($cant_notas > 0) ? number_format($suma_notas / $cant_notas, 1) : '-';

// --- GENERAR EL HTML QUE SE VERÁ EN EL MODAL ---
?>

<div class="row mb-3">
    <div class="col-6 text-center border-end">
        <h6 class="text-muted">Asistencia</h6>
        <h2 class="<?php echo ($porcentaje < 85) ? 'text-danger' : 'text-success'; ?> fw-bold">
            <?php echo $porcentaje; ?>%
        </h2>
        <div class="progress" style="height: 10px;">
            <div class="progress-bar <?php echo $color_barra; ?>" style="width: <?php echo $porcentaje; ?>%"></div>
        </div>
    </div>
    <div class="col-6 text-center">
        <h6 class="text-muted">Promedio Actual</h6>
        <h2 class="text-primary fw-bold"><?php echo $promedio; ?></h2>
    </div>
</div>

<h6 class="border-bottom pb-2">Desglose de Notas</h6>
<?php if(count($notas) == 0): ?>
    <p class="text-muted small text-center">No hay notas registradas aún.</p>
<?php else: ?>
    <table class="table table-sm table-striped">
        <thead><tr><th>Evaluación</th><th class="text-end">Nota</th></tr></thead>
        <tbody>
            <?php foreach($notas as $nota): ?>
                <tr>
                    <td><?php echo $nota['titulo']; ?></td>
                    <td class="text-end fw-bold">
                        <?php echo ($nota['nota']) ? $nota['nota'] : '<span class="text-muted small">Pendiente</span>'; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>