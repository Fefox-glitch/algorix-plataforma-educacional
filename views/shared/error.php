<?php
// Plantilla compartida para render_error(view: shared/error)
$code = isset($code) ? (int)$code : (http_response_code() ?: 500);
$message = isset($message) && $message !== '' ? $message : 'Ha ocurrido un error inesperado.';
?>
<div class="error-container" style="display:grid;place-items:center;padding:40px 16px">
  <div class="error-card" style="max-width:640px;width:100%;text-align:center;background:#0b1220;border:1px solid #1f2937;border-radius:16px;padding:28px;box-shadow:0 10px 30px rgba(0,0,0,0.35)">
    <div class="error-icon" aria-hidden="true" style="display:inline-grid;place-items:center;width:60px;height:60px;border-radius:50%;background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.35);color:#ef4444;font-size:32px;margin:0 auto 12px">!</div>
    <h1 style="margin:8px 0 6px;color:#e5e7eb;font-size:22px;font-weight:700"><?php echo htmlspecialchars($message); ?></h1>
    <p style="margin:0;color:#94a3b8;font-size:15px">Código: <?php echo htmlspecialchars((string)$code); ?></p>
    <div style="margin-top:18px;display:flex;gap:12px;justify-content:center">
      <a href="/" style="display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:10px;border:1px solid #334155;background:#0f172a;color:#cbd5e1;text-decoration:none;font-weight:600">Volver al inicio</a>
      <a href="javascript:location.reload()" style="display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:10px;border:1px solid #475569;background:#1e293b;color:#cbd5e1;text-decoration:none;font-weight:600">Reintentar</a>
    </div>
  </div>
</div>