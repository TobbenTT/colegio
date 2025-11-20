<div class="sidebar">
    <div class="logo mb-4"><i class="bi bi-bank2"></i> Dirección</div>
    
    <a href="dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
        <i class="bi bi-grid-fill"></i> <span>Menú Principal</span>
    </a>
    <a href="resumen.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'resumen.php') ? 'active' : ''; ?>">
        <i class="bi bi-bar-chart-line-fill"></i> <span>Estadísticas</span>
    </a>
    <a href="profesores.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'profesores.php') ? 'active' : ''; ?>">
        <i class="bi bi-people"></i> <span>Cuerpo Docente</span>
    </a>
    <a href="reportes.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'reportes.php') ? 'active' : ''; ?>">
        <i class="bi bi-file-earmark-text-fill"></i> <span>Reportes</span>
    </a>

    <a href="../notificaciones.php" class="d-flex justify-content-between align-items-center pe-3">
        <span><i class="bi bi-bell-fill"></i> Avisos</span>
        <?php if(isset($notificaciones_pendientes) && $notificaciones_pendientes > 0): ?>
            <span class="badge bg-danger rounded-pill shadow-sm">
                <?php echo $notificaciones_pendientes; ?>
            </span>
        <?php endif; ?>
    </a>

    <div class="mt-5">
        <a href="../logout.php" class="text-danger"><i class="bi bi-box-arrow-left"></i> <span>Salir</span></a>
    </div>
</div>