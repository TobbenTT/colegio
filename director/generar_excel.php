<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'director') { header("Location: ../login.php"); exit; }

// RECIBIR FILTROS
$curso_id = $_POST['curso_id'] ?? 'todos';
$estado_filtro = $_POST['estado'] ?? 'todos'; // todos, riesgo, aprobados

// CONSTRUIR NOMBRE DEL ARCHIVO
$nombre_archivo = "Reporte_Colegio_" . date('Y-m-d_H-i');
if($curso_id != 'todos') $nombre_archivo .= "_CursoID_".$curso_id;

// CABECERAS PARA FORZAR DESCARGA EXCEL
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=$nombre_archivo.xls");
header("Pragma: no-cache");
header("Expires: 0");

// CONSULTA SQL DINÁMICA
// Base de la consulta (Trae promedios y asistencia)
$sql = "SELECT 
            u.nombre, 
            u.email,
            c.nombre as curso,
            (SELECT AVG(nota) FROM entregas WHERE alumno_id = u.id AND nota > 0) as promedio,
            (SELECT COUNT(*) FROM asistencia WHERE alumno_id = u.id) as total_clases,
            (SELECT COUNT(*) FROM asistencia WHERE alumno_id = u.id AND estado='presente') as total_presente
        FROM usuarios u
        JOIN matriculas m ON u.id = m.alumno_id
        JOIN cursos c ON m.curso_id = c.id
        WHERE u.rol = 'alumno'";

$params = [];

// APLICAR FILTRO DE CURSO
if ($curso_id != 'todos') {
    $sql .= " AND c.id = ?";
    $params[] = $curso_id;
}

$sql .= " ORDER BY c.nombre, u.nombre";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$datos = $stmt->fetchAll();

// --- IMPRIMIR LA TABLA (QUE EXCEL INTERPRETA) ---
?>
<meta charset="UTF-8">
<table border="1">
    <thead>
        <tr style="background-color: #4CAF50; color: white;">
            <th style="width: 200px;">Nombre del Estudiante</th>
            <th style="width: 250px;">Correo</th>
            <th style="width: 100px;">Curso</th>
            <th style="width: 100px;">Promedio</th>
            <th style="width: 100px;">Asistencia %</th>
            <th style="width: 150px;">Estado Académico</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($datos as $d): ?>
            <?php 
                // Cálculos
                $prom = $d['promedio'] ? number_format($d['promedio'], 1) : '0.0';
                $pct = ($d['total_clases'] > 0) ? round(($d['total_presente']/$d['total_clases'])*100) : 100;
                
                // Lógica de Estado
                $es_riesgo = ($prom < 4.0 || $pct < 85);
                $estado_texto = $es_riesgo ? "EN RIESGO" : "APROBADO";
                
                // Colores de fondo para Excel
                $bg_color = $es_riesgo ? 'style="background-color: #ffcccc; color: #b30000;"' : '';

                // FILTRO FINAL EN PHP (Para riesgo/aprobado)
                // Si el usuario pidió "Solo Riesgo" y este alumno NO es riesgo, lo saltamos.
                if ($estado_filtro == 'riesgo' && !$es_riesgo) continue;
                if ($estado_filtro == 'aprobados' && $es_riesgo) continue;
            ?>
            <tr>
                <td><?php echo $d['nombre']; ?></td>
                <td><?php echo $d['email']; ?></td>
                <td style="text-align: center;"><?php echo $d['curso']; ?></td>
                
                <td style="text-align: center; font-weight: bold;"><?php echo str_replace('.', ',', $prom); ?></td>
                
                <td style="text-align: center;"><?php echo $pct; ?>%</td>
                <td <?php echo $bg_color; ?> align="center"><strong><?php echo $estado_texto; ?></strong></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>