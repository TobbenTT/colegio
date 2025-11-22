<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'director') { header("Location: ../login.php"); exit; }

// Obtener Cursos para el filtro
$cursos = $pdo->query("SELECT * FROM cursos ORDER BY nombre")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Zona de Descargas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

    <?php include '../includes/sidebar_director.php'; ?>


    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold text-dark"><i class="bi bi-file-earmark-excel-fill text-success"></i> Exportar Datos</h2>
                <p class="text-muted">Genera planillas Excel personalizadas.</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill">Volver al Inicio</a>
        </div>

        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="card shadow border-0">
                    <div class="card-header bg-success text-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-filter-circle"></i> Configurar Reporte</h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <form action="generar_excel.php" method="POST">
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold text-secondary">1. Selecciona el Curso</label>
                                <select name="curso_id" class="form-select form-select-lg border-success" required>
                                    <option value="todos">Todos los Cursos (Reporte General)</option>
                                    <?php foreach($cursos as $c): ?>
                                        <option value="<?php echo $c['id']; ?>"><?php echo $c['nombre']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-secondary">2. Filtrar por Estado Académico</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="estado" value="todos" checked>
                                        <label class="form-check-label">Todos</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="estado" value="riesgo">
                                        <label class="form-check-label text-danger fw-bold">Solo en Riesgo</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="estado" value="aprobados">
                                        <label class="form-check-label text-success fw-bold">Solo Aprobados</label>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg fw-bold shadow-sm">
                                    <i class="bi bi-download"></i> DESCARGAR PLANILLA EXCEL
                                </button>
                            </div>
                            <p class="text-muted text-center mt-3 small">El archivo se generará automáticamente en formato .xls</p>
                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>

</body>
</html>