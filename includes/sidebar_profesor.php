<div class="sidebar">
    <div class="logo mb-4"><i class="bi bi-mortarboard-fill"></i> ColegioApp</div>
    
    <a href="dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
        <i class="bi bi-speedometer2"></i> <span>Mis Cursos</span>
    </a>
    <a href="mensajes.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'mensajes.php') ? 'active' : ''; ?>">
        <i class="bi bi-chat-dots"></i> <span>Mensajería</span>
    </a>
    <a href="perfil.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'perfil.php') ? 'active' : ''; ?>">
        <i class="bi bi-person-circle"></i> <span>Mi Perfil</span>
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