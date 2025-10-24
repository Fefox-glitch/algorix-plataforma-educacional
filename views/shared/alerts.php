<?php
// Centraliza los mensajes de sesión para vistas
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$messageType = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : '';
// Limpiar después de leer para evitar repetición
unset($_SESSION['message'], $_SESSION['message_type']);

if ($message): ?>
<div id="authMessage" class="auth-message <?php echo ($messageType === 'error') ? 'error' : 'success'; ?>">
  <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>