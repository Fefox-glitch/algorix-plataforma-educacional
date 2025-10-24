<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Preview Panel de Profesor</title>
  <link rel="stylesheet" href="/styles/base.css" />
  <link rel="stylesheet" href="/styles/header.css" />
  <link rel="stylesheet" href="/styles/components.css" />
  <link rel="stylesheet" href="/styles/teacher.css" />
  <link rel="stylesheet" href="/styles/theme-switcher.css" />
</head>
<body data-theme="dark">
  <?php include __DIR__ . '/../views/shared/header.php'; ?>

  <div class="teacher-container">
    <aside class="teacher-sidebar">
      <div class="dashboard-card" style="background: linear-gradient(135deg, rgba(16,185,129,.25), rgba(16,185,129,.12)); border: 2px solid rgba(16,185,129,.35); border-radius: var(--radius); padding: 20px;">
        <h3>Tu Compañero</h3>
        <p>Estoy aquí para ayudarte a aprender programación paso a paso.</p>
        <div class="helper-status" style="display:flex; align-items:center; gap:8px;">
          <span class="helper-pulse"></span>
          <span>Tu ayudante listo</span>
        </div>
      </div>

      <div class="dashboard-card" style="background: var(--bg-primary); border: 2px solid var(--border-color); border-radius: var(--radius); padding: 20px;">
        <h3>Crear Ejercicio Nuevo</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
          <button class="btn btn-primary">Plantilla básica</button>
          <button class="btn btn-secondary">Desde reto</button>
          <button class="btn btn-secondary">Importar</button>
          <button class="btn btn-secondary">Duplicar</button>
        </div>
      </div>
    </aside>

    <main class="teacher-content">
      <nav class="teacher-nav" style="display:flex; gap:10px; margin-bottom:12px;">
        <a href="#main-panel" class="nav-item active" data-section="main-panel">Panel Principal</a>
        <a href="#courses" class="nav-item" data-section="courses">Cursos</a>
        <a href="#groups" class="nav-item" data-section="groups">Grupos</a>
        <a href="#assignments" class="nav-item" data-section="assignments">Actividades</a>
      </nav>

      <section id="main-panel" class="section active">
        <div class="section-header">
          <h1>Panel Principal</h1>
        </div>

        <div class="hero-banner" style="margin-bottom: 20px;">
          <div style="opacity: 0.2; font-size: 32px; font-weight: 800;">Algorix</div>
          <div style="display:flex; align-items:center; gap: 10px;">
            <span style="font-weight:700;">¡Hola, profesor!</span>
            <span class="role-badge teacher-badge">PROFESOR</span>
            <a href="#" class="btn btn-secondary">Salir</a>
          </div>
        </div>

        <div class="advanced-panel">
          <div class="panel-title"><span class="dot"></span> Panel de Control Avanzado</div>
          <div class="advanced-grid">
            <a class="advanced-card is-green" href="#users">
              <span class="icon">👥</span>
              <div>
                <h4>Gestión de Usuarios</h4>
                <p>Administra estudiantes y profesores</p>
              </div>
            </a>
            <a class="advanced-card is-purple" href="#analytics-advanced">
              <span class="icon">📊</span>
              <div>
                <h4>Analytics Avanzado</h4>
                <p>Métricas detalladas de rendimiento</p>
              </div>
            </a>
            <a class="advanced-card is-blue" href="#grades">
              <span class="icon">🧮</span>
              <div>
                <h4>Sistema de Notas</h4>
                <p>Calificaciones y reportes</p>
              </div>
            </a>
            <a class="advanced-card is-orange" href="#export">
              <span class="icon">📤</span>
              <div>
                <h4>Centro de Exportación</h4>
                <p>CSV, PDF y más</p>
              </div>
            </a>
            <a class="advanced-card is-gray" href="#settings">
              <span class="icon">⚙️</span>
              <div>
                <h4>Configuración</h4>
                <p>Preferencias del panel</p>
              </div>
            </a>
            <a class="advanced-card is-orange" href="#codes">
              <span class="icon">🧩</span>
              <div>
                <h4>Gestión de Códigos</h4>
                <p>Ejercicios y retos</p>
              </div>
            </a>
            <a class="advanced-card is-teal" href="#communication">
              <span class="icon">💬</span>
              <div>
                <h4>Comunicación</h4>
                <p>Anuncios y mensajes</p>
              </div>
            </a>
            <a class="advanced-card is-teal" href="#analytics-ai">
              <span class="icon">🤖</span>
              <div>
                <h4>Analytics IA</h4>
                <p>Insights asistidos</p>
              </div>
            </a>
          </div>
        </div>

        <div class="card" style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius); padding: 16px; margin-top: 16px;">
          <h3>Resultado</h3>
          <pre id="teacher-output" style="white-space: pre-wrap; max-height: 280px; overflow:auto;">—</pre>
        </div>
      </section>
    </main>
  </div>

  <script>
  // Lógica de cambio de tema (light/dark) para preview PHP
  (function() {
    var themeSwitch = document.getElementById('themeSwitch');
    var applyTheme = function(theme) {
      document.body.setAttribute('data-theme', theme);
      if (themeSwitch) {
        if (theme === 'dark') {
          themeSwitch.classList.add('dark');
        } else {
          themeSwitch.classList.remove('dark');
        }
      }
      try { localStorage.setItem('theme', theme); } catch (e) {}
    };

    // Aplicar tema guardado
    try {
      var saved = localStorage.getItem('theme');
      if (saved) applyTheme(saved);
    } catch (e) {}

    // Toggle al hacer clic
    if (themeSwitch) {
      themeSwitch.addEventListener('click', function() {
        var current = document.body.getAttribute('data-theme') || 'light';
        applyTheme(current === 'light' ? 'dark' : 'light');
      });
    }
  })();
  </script>
  <script src="/js/teacher-dashboard.js"></script>
</body>
</html>