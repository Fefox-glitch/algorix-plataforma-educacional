<?php
// Plantilla genérica de error para producción
$code = isset($code) ? (int)$code : http_response_code();
if ($code === 0) { $code = 503; http_response_code(503); }
$message = isset($message) && $message !== '' ? $message : 'Servicio no disponible.';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Error <?php echo htmlspecialchars($code); ?> - Algorix</title>
  <style>
    :root {
      --bg: #0f172a;
      --text: #e5e7eb;
      --muted: #94a3b8;
      --accent: #ef4444;
      --card: #111827;
    }
    html, body { height: 100%; }
    body { margin:0; background: var(--bg); color: var(--text); font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif; }
    .wrap { min-height: 100%; display: grid; place-items: center; padding: 24px; }
    .card { background: var(--card); border: 1px solid #1f2937; border-radius: 16px; padding: 32px 28px; max-width: 520px; width: 100%; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.35); }
    .icon { display:inline-grid; place-items:center; width:64px; height:64px; border-radius:50%; background: rgba(239, 68, 68, 0.12); border:1px solid rgba(239,68,68,0.35); color: var(--accent); font-size: 34px; margin: 0 auto 16px; }
    h1 { margin: 8px 0 4px; font-size: 22px; font-weight: 700; letter-spacing: 0.2px; }
    p { margin: 0; font-size: 15px; color: var(--muted); }
    .actions { margin-top: 20px; display:flex; gap:12px; justify-content:center; }
    .btn { display:inline-flex; align-items:center; gap:10px; padding:10px 14px; border-radius: 10px; border:1px solid #334155; background:#0b1220; color:#cbd5e1; text-decoration:none; font-weight:600; }
    .btn.primary { background:#1e293b; border-color:#475569; }
    .btn:hover { filter: brightness(1.08); }
    .code { margin-top: 12px; font-size: 12px; color: #64748b; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card" role="alert" aria-live="polite">
      <div class="icon" aria-hidden="true">!</div>
      <h1><?php echo htmlspecialchars($message); ?></h1>
      <p>Intenta de nuevo en unos minutos o vuelve al inicio.</p>
      <div class="actions">
        <a class="btn" href="/">Ir al inicio</a>
        <a class="btn primary" href="javascript:location.reload()">Reintentar</a>
      </div>
      <div class="code">Código: <?php echo htmlspecialchars((string)$code); ?></div>
    </div>
  </div>
</body>
</html>