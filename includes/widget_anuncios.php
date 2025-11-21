<?php
// Obtener últimos 3 anuncios
$anuncios = $pdo->query("SELECT * FROM anuncios ORDER BY fecha DESC LIMIT 3")->fetchAll();
?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-newspaper"></i> Cartelera Institucional</h5>
        <span class="badge bg-light text-dark border">Novedades</span>
    </div>
    <div class="card-body p-0">
        <?php if(count($anuncios) == 0): ?>
            <div class="text-center py-4 text-muted small">No hay anuncios recientes.</div>
        <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach($anuncios as $an): ?>
                    <?php 
                        $borde = ($an['tipo'] == 'urgente') ? 'border-danger' : 'border-info';
                        $icono = ($an['tipo'] == 'urgente') ? '<i class="bi bi-exclamation-circle-fill text-danger"></i>' : '<i class="bi bi-info-circle-fill text-info"></i>';
                    ?>
                    <div class="list-group-item p-3 border-start border-4 <?php echo $borde; ?>">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1 fw-bold"><?php echo $icono . " " . e($an['titulo']); ?></h6>
                            <small class="text-muted"><?php echo date("d/m", strtotime($an['fecha'])); ?></small>
                        </div>
                        <p class="mb-1 small text-secondary"><?php echo e($an['mensaje']); ?></p>
                        <small class="text-muted fst-italic" style="font-size: 0.7rem;">Por Dirección</small>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>