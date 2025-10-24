<div class="auth-screen">
    <div class="auth-header">
        <h2 class="auth-title" id="authTitle">¡Hola! 👋</h2>
        <p class="auth-subtitle">Vamos a aprender programación de forma divertida</p>
    </div>

    <div class="welcome-notice">
        <h4>🌟 ¡Bienvenido a tu aventura de programación!</h4>
        <p>Aquí aprenderás paso a paso, sin prisa y con mucha diversión.</p>
        <p class="codes-info"><strong>Para profesores:</strong> TEACH2024A1 | <strong>Para administradores:</strong> ADMIN2024B1</p>
    </div>
    
    <!-- Login Form -->
    <div id="loginForm">
        <form action="<?php echo base_url('auth/login'); ?>" method="POST">
            <?php
            // Generar token CSRF
            if (class_exists('App\\Core\\Security')) {
                $csrf = App\Core\Security::generateCsrfToken();
            } else {
                if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
                $csrf = $_SESSION['csrf_token'];
            }
            ?>
            <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
            <div class="form-group">
                <label class="form-label">Tu email:</label>
                <input type="email" name="email" class="form-input" placeholder="ejemplo@colegio.edu" required>
            </div>
            <div class="form-group">
                <label class="form-label">Tu contraseña:</label>
                <input type="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>
            <div class="forgot-password">
                <a href="<?php echo base_url('auth/recover'); ?>">¿Olvidaste tu contraseña?</a>
            </div>
            <button type="submit" class="btn btn-primary btn-full">🚀 ¡Empezar!</button>
        </form>
        <a href="<?php echo base_url('auth/register'); ?>" class="btn btn-secondary btn-full">Crear cuenta nueva</a>
    </div>

    <?php include __DIR__ . '/../shared/alerts.php'; ?>
</div>