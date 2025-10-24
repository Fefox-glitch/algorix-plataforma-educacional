<div class="app-container">
    <!-- Global Header -->
    <header class="main-header">
        <div class="logo-section">
            <div class="logo">Algorix</div>
            <div class="friendly-badge">Sistema Completo</div>
            <div class="institution-name">Plataforma Educativa Avanzada</div>
        </div>
        <div class="header-controls" style="display: flex; align-items: center; gap: 15px;">
            <?php 
                $role = isset($userRole) ? $userRole : '';
                $defaultName = ($role === 'admin') ? 'Administrador' : (($role === 'teacher') ? 'Profesor' : 'Estudiante');
                $displayName = isset($_SESSION['user']['name']) ? htmlspecialchars($_SESSION['user']['name']) : $defaultName;
                if (in_array($role, ['admin','teacher','student'])): ?>
                <div class="user-info">
                    <span class="role-badge <?php echo $role; ?>-badge"><?php echo strtoupper($role); ?></span>
                    <span class="welcome-text">Bienvenido, <?php echo $displayName; ?></span>
                </div>
            <?php endif; ?>
            <div class="theme-switcher" id="themeSwitch">
                <span class="theme-switch-inner"></span>
                <span class="theme-icon light-icon">☀️</span>
                <span class="theme-icon dark-icon">🌙</span>
            </div>
            <button class="btn btn-secondary" onclick="showHelp()" id="helpButton">Ayuda</button>
            <?php if (in_array($role, ['admin','teacher','student'])): ?>
                <a href="<?php echo base_url('auth/logout'); ?>" class="btn btn-secondary">Salir</a>
            <?php endif; ?>
        </div>
    </header>
</div>

<!-- Modal de Ayuda -->
<div id="helpModal" class="modal" aria-hidden="true" style="display:none">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Centro de Ayuda</h2>
            <button type="button" class="modal-close" aria-label="Cerrar">&times;</button>
        </div>
        <div class="modal-body">
            <div class="help-section">
                <h3>🎯 Primeros Pasos</h3>
                <ul>
                    <li>Registra una cuenta para comenzar</li>
                    <li>Selecciona tu rol (Estudiante, Profesor o Director)</li>
                    <li>Explora los cursos disponibles</li>
                </ul>
            </div>
            <div class="help-section">
                <h3>🎮 Características</h3>
                <ul>
                    <li>Aprende programación con ejercicios interactivos</li>
                    <li>Gana puntos y desbloquea logros</li>
                    <li>Sigue tu progreso en tiempo real</li>
                </ul>
            </div>
            <div class="help-section">
                <h3>📞 Soporte</h3>
                <p>¿Necesitas ayuda adicional? Contáctanos en:</p>
                <a href="mailto:soporte@algorix.edu" class="support-link">soporte@algorix.edu</a>
            </div>
        </div>
    </div>
</div>