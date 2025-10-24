<div class="auth-screen auth-register">
    <div class="auth-header">
        <h2 class="auth-title">¡Crea tu cuenta! 🎉</h2>
        <p class="auth-subtitle">Únete a la aventura de programación</p>
    </div>

    <form action="<?php echo base_url('auth/register'); ?>" method="POST" id="registerForm">
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
            <label class="form-label">Tu nombre:</label>
            <input type="text" name="name" class="form-input" placeholder="Escribe tu nombre aquí" required>
        </div>
        <div class="form-group">
            <label class="form-label">Tu email:</label>
            <input type="email" name="email" class="form-input" placeholder="tuemail@colegio.edu" required>
        </div>
        <div class="form-group">
            <label class="form-label">Crea una contraseña:</label>
            <input type="password" name="password" class="form-input" placeholder="Elige una contraseña segura" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">¿Quién eres?</label>
            <div class="role-selector">
                <div class="role-option selected" data-role="student">
                    <div class="role-icon">👨‍🎓</div>
                    <div class="role-name">Estudiante</div>
                    <div class="role-desc">¡A aprender!</div>
                    <input type="radio" name="role" value="student" checked>
                </div>
                <div class="role-option" data-role="teacher">
                    <div class="role-icon">👩‍🏫</div>
                    <div class="role-name">Profesor</div>
                    <div class="role-desc">Guía</div>
                    <input type="radio" name="role" value="teacher">
                </div>
                <div class="role-option" data-role="admin">
                    <div class="role-icon">👨‍💼</div>
                    <div class="role-name">Director</div>
                    <div class="role-desc">Admin</div>
                    <input type="radio" name="role" value="admin">
                </div>
            </div>
        </div>

        <div class="form-group access-code-field" id="accessCodeField">
            <label class="form-label">Código especial:</label>
            <input type="text" name="accessCode" class="form-input" placeholder="Pide el código a tu director" style="text-transform: uppercase;">
            <div style="font-size: 12px; margin-top: 5px;">
                <span id="codeStrength">Necesitas un código especial</span>
                <a href="<?php echo base_url('help/access-code'); ?>" style="color: var(--friendly-color); cursor: pointer; float: right;">¿Necesitas ayuda?</a>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Tu grupo (opcional):</label>
            <input type="text" name="group" class="form-input" placeholder="Ejemplo: 3°A">
        </div>
        
        <button type="submit" class="btn btn-primary btn-full">🎉 ¡Crear mi cuenta!</button>
    </form>
    <a href="<?php echo base_url('auth/login'); ?>" class="btn btn-secondary btn-full">Ya tengo cuenta</a>

    <?php include __DIR__ . '/../shared/alerts.php'; ?>
</div>

<script>
function selectRole(role) {
    document.querySelectorAll('.role-option').forEach(option => {
        option.classList.remove('selected');
        option.querySelector('input[type="radio"]').checked = false;
    });
    
    const selectedOption = document.querySelector(`.role-option[data-role="${role}"]`);
    selectedOption.classList.add('selected');
    selectedOption.querySelector('input[type="radio"]').checked = true;
    
    const accessCodeField = document.getElementById('accessCodeField');
    if (role === 'teacher' || role === 'admin') {
        accessCodeField.classList.add('show');
    } else {
        accessCodeField.classList.remove('show');
    }
}

// Activar selección por clic
Array.from(document.querySelectorAll('.role-option')).forEach(opt => {
    opt.addEventListener('click', () => selectRole(opt.getAttribute('data-role')));
});
</script>