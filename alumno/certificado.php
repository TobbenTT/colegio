<?php
session_start();
require '../config/db.php';
require '../includes/funciones.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'alumno') { header("Location: ../login.php"); exit; }

$id = $_SESSION['user_id'];
$fecha_hoy = date("d/m/Y");

// DATOS ALUMNO Y CURSO
$sqlInfo = "SELECT u.nombre, u.email, c.nombre as curso 
            FROM usuarios u 
            JOIN matriculas m ON u.id = m.alumno_id 
            JOIN cursos c ON m.curso_id = c.id 
            WHERE u.id = ?";
$stmt = $pdo->prepare($sqlInfo);
$stmt->execute([$id]);
$alumno = $stmt->fetch();

// NOTAS
$sqlNotas = "SELECT a.nombre as materia, AVG(e.nota) as promedio
             FROM programacion_academica pa
             JOIN asignaturas a ON pa.asignatura_id = a.id
             JOIN actividades act ON pa.id = act.programacion_id
             LEFT JOIN entregas e ON act.id = e.actividad_id AND e.alumno_id = ?
             WHERE pa.curso_id = (SELECT curso_id FROM matriculas WHERE alumno_id = ?)
             GROUP BY a.id";
$stmt = $pdo->prepare($sqlNotas);
$stmt->execute([$id, $id]);
$notas = $stmt->fetchAll();

// PROMEDIO GENERAL
$suma = 0; $count = 0;
foreach($notas as $n) { if($n['promedio'] > 0) { $suma += $n['promedio']; $count++; } }
$promedio_final = ($count > 0) ? number_format($suma/$count, 1) : '0.0';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado de Notas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #525659; padding: 30px; font-family: 'Times New Roman', serif; }
        .hoja {
            background: white; width: 21cm; min-height: 29.7cm; margin: 0 auto; padding: 2cm;
            box-shadow: 0 0 10px rgba(0,0,0,0.5); position: relative;
        }
        @media print {
            body { background: white; padding: 0; }
            .hoja { box-shadow: none; margin: 0; width: 100%; }
            .no-print { display: none !important; }
        }
        .membrete { border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 40px; }
        .firma { margin-top: 100px; text-align: center; }
        .linea-firma { border-top: 1px solid #000; width: 200px; margin: 0 auto 10px auto; }
    </style>
</head>
<body>

    <div class="text-center mb-4 no-print">
        <a href="resumen_academico.php" class="btn btn-secondary text-white fw-bold rounded-pill px-4">Volver</a>
        <button onclick="window.print()" class="btn btn-primary fw-bold rounded-pill px-4">Descargar PDF</button>
    </div>

    <div class="hoja">
        <div class="membrete d-flex justify-content-between align-items-center">
            <div>
                <img src="https://cdn-icons-png.flaticon.com/512/167/167707.png" width="80">
            </div>
            <div class="text-end">
                <h2 class="fw-bold m-0">COLEGIO INSTITUCIONAL</h2>
                <p class="m-0">Reconocido por el Ministerio de Educación</p>
                <small>RBD: 12345-6 | Av. Siempre Viva 742</small>
            </div>
        </div>

        <h3 class="text-center fw-bold text-uppercase mb-5" style="text-decoration: underline;">Certificado de Notas</h3>

        <p class="fs-5 mb-4" style="line-height: 2;">
            La Dirección del Establecimiento certifica que el alumno(a) <strong><?php echo strtoupper($alumno['nombre']); ?></strong>, 
            Rut: 12.345.678-9, ha cursado regularmente el <strong><?php echo $alumno['curso']; ?></strong> durante el año escolar 
            <strong><?php echo date('Y'); ?></strong>, obteniendo las siguientes calificaciones parciales:
        </p>

        <table class="table table-bordered border-dark mb-4">
            <thead class="table-light border-dark">
                <tr>
                    <th>Asignatura</th>
                    <th class="text-center" width="150">Promedio Final</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($notas as $n): ?>
                    <?php $nota = number_format($n['promedio'], 1); ?>
                    <tr>
                        <td><?php echo $n['materia']; ?></td>
                        <td class="text-center fw-bold"><?php echo $nota; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-light border-dark">
                <tr>
                    <td class="text-end fw-bold">PROMEDIO GENERAL:</td>
                    <td class="text-center fw-bold fs-5"><?php echo $promedio_final; ?></td>
                </tr>
            </tfoot>
        </table>

        <p class="mt-5">
            Se extiende el presente certificado a petición del interesado para los fines que estime conveniente.
        </p>

        <p class="text-end mt-5">
            <em>Santiago, <?php echo $fecha_hoy; ?></em>
        </p>

        <div class="firma">
            <div class="linea-firma"></div>
            <p class="fw-bold mb-0">Sra. Directora</p>
            <small>Directora Académica</small>
        </div>
        
        <img src="https://cdn-icons-png.flaticon.com/512/5610/5610944.png" style="position: absolute; bottom: 180px; right: 150px; width: 120px; opacity: 0.6; transform: rotate(-15deg);">

    </div>

</body>
</html>