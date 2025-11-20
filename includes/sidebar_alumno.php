<div class="sidebar">
    <div class="logo mb-4"><i class="bi bi-backpack2-fill"></i> Mi Colegio</div>
    
    <a href="dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
        <i class="bi bi-grid-fill"></i> <span>Mis Clases</span>
    </a>
    <a href="horario.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'horario.php') ? 'active' : ''; ?>">
        <i class="bi bi-calendar-week"></i> <span>Horario</span>
    </a>
    <a href="asistencia.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'asistencia.php') ? 'active' : ''; ?>">
        <i class="bi bi-clipboard-check"></i> <span>Asistencia</span>
    </a>
    <a href="mis_anotaciones.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'mis_anotaciones.php') ? 'active' : ''; ?>">
        <i class="bi bi-exclamation-triangle"></i> <span>Hoja de Vida</span>
    </a>
    <a href="perfil.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'perfil.php') ? 'active' : ''; ?>">
        <i class="bi bi-person-circle"></i> <span>Mi Perfil</span>
    </a>

    <?php 
        // Lógica visual: ¿Hay notificaciones?
        $hay_avisos = (isset($notificaciones_pendientes) && $notificaciones_pendientes > 0);
        
        // Si hay avisos: Texto amarillo
        $clase_extra = $hay_avisos ? 'text-warning fw-bold' : ''; 
        
        // Si hay avisos: Icono relleno y animación 'swing' (balanceo)
        $icono = $hay_avisos ? 'bi-bell-fill animate__animated animate__swing animate__infinite' : 'bi-bell'; 
    ?>

    <a href="../notificaciones.php" class="d-flex justify-content-between align-items-center pe-3 <?php echo $clase_extra; ?>">
        <span>
            <i class="bi <?php echo $icono; ?>"></i> 
            Avisos
        </span>
        
        <?php if($hay_avisos): ?>
            <span class="badge bg-danger rounded-pill shadow-sm border border-light">
                <?php echo $notificaciones_pendientes; ?>
            </span>
        <?php endif; ?>
    </a>
    
    <div class="mt-5">
        <a href="../logout.php" class="text-danger"><i class="bi bi-box-arrow-left"></i> <span>Salir</span></a>
    </div>
</div>