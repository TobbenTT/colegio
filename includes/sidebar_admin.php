<div class="sidebar" id="sidebarMenu">
    <div class="d-flex justify-content-end d-md-none p-2">
        <button class="btn text-white fs-4" onclick="toggleMenu()"><i class="bi bi-x-lg"></i></button>
    </div>

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
    <a href="familia.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'familia.php') ? 'active' : ''; ?>">
        <i class="bi bi-people"></i> <span>Familias</span>
    </a>
    <a href="cursos.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'cursos.php') ? 'active' : ''; ?>">
        <i class="bi bi-building"></i> <span>Cursos y Materias</span>
    </a>
    <a href="asignacion.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'asignacion.php') ? 'active' : ''; ?>">
        <i class="bi bi-diagram-3-fill"></i> <span>Carga Académica</span>
    </a>
    <a href="calendario_gestion.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'calendario_gestion.php') ? 'active' : ''; ?>">
        <i class="bi bi-calendar-check"></i> <span>Gestión Calendario</span>
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

<script>function toggleMenu() { document.getElementById('sidebarMenu').classList.toggle('active'); }</script>