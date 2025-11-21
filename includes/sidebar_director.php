<div class="sidebar" id="sidebarMenu">
    
    <div class="d-flex justify-content-end d-md-none p-2">
        <button class="btn text-white fs-4" onclick="toggleMenu()"><i class="bi bi-x-lg"></i></button>
    </div>

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
    <a href="riesgo_escolar.php" class="text-warning fw-bold">
        <i class="bi bi-heart-pulse-fill"></i> <span>IA Riesgo Escolar</span>
    </a>
    <a href="zona_descargas.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'zona_descargas.php') ? 'active' : ''; ?>">
        <i class="bi bi-cloud-download-fill"></i> <span>Exportar Datos</span>
    </a>
    <?php 
        $hay_avisos = (isset($notificaciones_pendientes) && $notificaciones_pendientes > 0);
        $clase_link = $hay_avisos ? 'text-warning fw-bold' : ''; 
        $icono = $hay_avisos ? 'bi-bell-fill animate__animated animate__swing animate__infinite' : 'bi-bell'; 
    ?>
    <a href="../notificaciones.php" class="d-flex justify-content-between align-items-center pe-3 <?php echo $clase_link; ?>">
        <span><i class="bi <?php echo $icono; ?>"></i> Avisos</span>
        <?php if($hay_avisos): ?>
            <span class="badge bg-danger rounded-pill shadow-sm border border-light"><?php echo $notificaciones_pendientes; ?></span>
        <?php endif; ?>
    </a>

    <div class="mt-5">
        <a href="../logout.php" class="text-danger"><i class="bi bi-box-arrow-left"></i> <span>Salir</span></a>
    </div>
</div>

<button class="mobile-nav-toggle d-md-none" onclick="toggleMenu()"><i class="bi bi-list"></i></button>

<script>
    function toggleMenu() {
        document.getElementById('sidebarMenu').classList.toggle('active');
    }
</script>