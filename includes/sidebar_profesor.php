<div class="sidebar" id="sidebarMenu">
    
    <div class="d-flex justify-content-end d-md-none p-2">
        <button class="btn text-white fs-4" onclick="toggleMenu()"><i class="bi bi-x-lg"></i></button>
    </div>

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
    <a href="escaner_qr.php" class="text-warning fw-bold">
        <i class="bi bi-qr-code-scan"></i> <span>Escanear QR</span>
    </a>
    <a href="mi_horario.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'mi_horario.php') ? 'active' : ''; ?>">
        <i class="bi bi-calendar-range"></i> <span>Gestionar Horario</span>
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