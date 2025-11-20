<div class="sidebar">
    <div class="logo mb-4"><i class="bi bi-shield-lock-fill"></i> AdminPanel</div>
    
    <a href="dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
        <i class="bi bi-speedometer2"></i> <span>Inicio</span>
    </a>
    
    <hr class="text-secondary mx-3 my-2">
    
    <a href="usuarios.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'usuarios.php') ? 'active' : ''; ?>">
        <i class="bi bi-people-fill"></i> <span>Usuarios</span>
    </a>
    <a href="matriculas.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'matriculas.php') ? 'active' : ''; ?>">
        <i class="bi bi-card-checklist"></i> <span>Matrículas</span>
    </a>
    <a href="cursos.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'cursos.php') ? 'active' : ''; ?>">
        <i class="bi bi-building"></i> <span>Cursos y Materias</span>
    </a>
    <a href="asignacion.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'asignacion.php') ? 'active' : ''; ?>">
        <i class="bi bi-diagram-3-fill"></i> <span>Carga Académica</span>
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