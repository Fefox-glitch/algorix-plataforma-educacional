<?php
// views/home.php
?>
<main class="home-content">
    <?php if (!isset($_SESSION['user'])): ?>
        <div class="welcome-section">
            <h1>¡Bienvenido a Algorix! 🚀</h1>
            <p class="welcome-subtitle">Aprende programación de forma divertida y efectiva</p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">👨‍💻</div>
                    <h3>Aprende Programando</h3>
                    <p>Practica con ejercicios interactivos y retos divertidos</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🎮</div>
                    <h3>Aprende Jugando</h3>
                    <p>Gana puntos y compite mientras aprendes</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Sigue tu Progreso</h3>
                    <p>Visualiza tu avance y mejora continua</p>
                </div>
            </div>

            <div class="cta-section">
                <a href="<?php echo base_url('auth/register'); ?>" class="btn btn-primary">Comenzar Ahora</a>
                <a href="<?php echo base_url('auth/login'); ?>" class="btn btn-secondary">Ya tengo cuenta</a>
            </div>
        </div>
    <?php else: ?>
        <div class="dashboard-container">
            <h2>¡Bienvenido, <?php echo htmlspecialchars($_SESSION['user']['name']); ?>!</h2>
            <p>Tu rol: <strong><?php echo htmlspecialchars($_SESSION['user']['role']); ?></strong></p>
            <div class="quick-actions">
                <?php if ($_SESSION['user']['role'] === 'student'): ?>
                    <a href="<?php echo base_url('student/dashboard'); ?>" class="btn btn-primary">Ir a mi Dashboard</a>
                <?php elseif ($_SESSION['user']['role'] === 'teacher'): ?>
                    <a href="<?php echo base_url('teacher/dashboard'); ?>" class="btn btn-primary">Ir a mi Dashboard</a>
                <?php elseif ($_SESSION['user']['role'] === 'admin'): ?>
                    <a href="<?php echo base_url('admin/dashboard'); ?>" class="btn btn-primary">Ir a mi Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</main>