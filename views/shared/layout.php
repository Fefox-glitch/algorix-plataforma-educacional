<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Algorix - Plataforma educativa para aprender programación de forma interactiva y divertida">
    <meta name="keywords" content="programación, educación, algoritmos, aprendizaje, coding">
    <meta name="author" content="Algorix">
    <meta name="theme-color" content="#4CAF50">
    
    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Algorix">
    <meta property="og:description" content="Aprende programación de forma interactiva y divertida">
    <meta property="og:image" content="<?php echo base_url('assets/logo.svg'); ?>">
    
    <title><?php echo $pageTitle; ?> - Algorix</title>
    
    <!-- Estilos -->
    <link rel="stylesheet" href="<?php echo base_url('styles/base.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('styles/typography.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('styles/components.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('styles/ui.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('styles/forms.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('styles/auth.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('styles/role-selector.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('styles/responsive.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('styles/home.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('styles/error.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('styles/header.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('styles/theme-switcher.css'); ?>">
    
    <?php 
        // Cargar CSS específico de rol solo si el archivo existe
        $roleStyleFile = __DIR__ . '/../../styles/' . (isset($userRole) ? $userRole : '') . '.css';
        if (isset($userRole) && $userRole !== '' && file_exists($roleStyleFile)) :
    ?>
        <link rel="stylesheet" href="<?php echo base_url('styles/' . $userRole . '.css'); ?>">
    <?php endif; ?>
    
    <!-- Scripts - Removed JS dependencies -->
</head>
<body data-theme="light">
    <?php if (isset($_ENV['OFFLINE_MODE']) && $_ENV['OFFLINE_MODE'] === 'true'): ?>
        <div class="offline-banner" role="status" aria-live="polite">
            <span class="icon" aria-hidden="true">!</span>
            <span class="text">Modo Offline activo: datos simulados (sin Supabase)</span>
            <a class="link" href="<?php echo base_url('test-connection.php'); ?>" target="_blank" rel="noopener">Probar conexión</a>
        </div>
        <style>
            .offline-banner{position:sticky;top:0;z-index:1000;background:#ffdd57;color:#111;padding:12px 16px;text-align:center;font-weight:800;box-shadow:0 2px 8px rgba(0,0,0,0.2);display:flex;align-items:center;justify-content:center;gap:12px;border-bottom:2px solid #e0b800}
            .offline-banner .icon{display:inline-grid;place-items:center;width:24px;height:24px;border-radius:50%;border:1px solid #c08a00;color:#c08a00;font-weight:800}
            .offline-banner .link{color:#0b5ed7;text-decoration:underline;font-weight:700}
        </style>
    <?php endif; ?>
    <!-- Header -->
    <?php include __DIR__ . '/header.php'; ?>

    <!-- Contenido principal -->
    <div class="main-container">
        <?php echo $content; ?>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <p>&copy; <?php echo date('Y'); ?> Algorix. Todos los derechos reservados.</p>
    </footer>

    <script>
    // Comportamiento del Centro de Ayuda (abrir/cerrar)
    (function() {
        var modal = document.getElementById('helpModal');
        var openBtn = document.getElementById('helpButton');
        var closeBtn = modal ? modal.querySelector('.modal-close') : null;

        function openModal() {
            if (!modal) return;
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            if (!modal) return;
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        if (openBtn) {
            openBtn.addEventListener('click', function(e) {
                e.preventDefault();
                openModal();
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                closeModal();
            });
        }

        if (modal) {
            // Cerrar al hacer click fuera del contenido
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });

            // Cerrar con la tecla Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.style.display !== 'none') {
                    closeModal();
                }
            });
        }

        // Exponer función global para botón inline
        window.showHelp = openModal;
    })();

    // Lógica de cambio de tema (light/dark)
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

        // Exponer función global para inline onclick
        window.toggleTheme = function() {
            var current = document.body.getAttribute('data-theme') || 'light';
            applyTheme(current === 'light' ? 'dark' : 'light');
        };
    })();
    </script>
    <script>
      window.CSRF_TOKEN = '<?php echo \App\Core\Security::generateCsrfToken(); ?>';
    </script>
    <?php if (isset($userRole) && ($userRole === 'student' || $userRole === 'teacher')): ?>
    <?php
      $aiConfigPath = __DIR__ . '/../../storage/ai_config.json';
      $cfg = [
        'endpoint' => isset($_ENV['AI_ENDPOINT']) ? $_ENV['AI_ENDPOINT'] : 'https://oi-server.onrender.com/chat/completions',
        'token' => isset($_ENV['AI_TOKEN']) ? $_ENV['AI_TOKEN'] : '',
        'customerId' => isset($_ENV['AI_CUSTOMER_ID']) ? $_ENV['AI_CUSTOMER_ID'] : '',
        'model' => isset($_ENV['AI_MODEL']) ? $_ENV['AI_MODEL'] : 'openrouter/claude-sonnet-4',
        'offline' => (isset($_ENV['OFFLINE_MODE']) && $_ENV['OFFLINE_MODE'] === 'true')
      ];
      if (file_exists($aiConfigPath)) {
        $fileCfgRaw = @file_get_contents($aiConfigPath);
        $fileCfg = @json_decode($fileCfgRaw, true);
        if (is_array($fileCfg)) {
          $cfg['endpoint'] = isset($fileCfg['AI_ENDPOINT']) && $fileCfg['AI_ENDPOINT'] !== '' ? $fileCfg['AI_ENDPOINT'] : $cfg['endpoint'];
          $cfg['token'] = isset($fileCfg['AI_TOKEN']) ? $fileCfg['AI_TOKEN'] : $cfg['token'];
          $cfg['customerId'] = isset($fileCfg['AI_CUSTOMER_ID']) ? $fileCfg['AI_CUSTOMER_ID'] : $cfg['customerId'];
          $cfg['model'] = isset($fileCfg['AI_MODEL']) && $fileCfg['AI_MODEL'] !== '' ? $fileCfg['AI_MODEL'] : $cfg['model'];
          if (isset($fileCfg['OFFLINE_MODE'])) {
            $cfg['offline'] = ($fileCfg['OFFLINE_MODE'] === true || $fileCfg['OFFLINE_MODE'] === 'true');
          }
        }
      }
    ?>
    <script>
      (function(){
        var params = new URLSearchParams(window.location.search);
        var module = params.get('module') || '';
        var role = "<?php echo isset($userRole) ? $userRole : ''; ?>";
        var offlineDefault = <?php echo $cfg['offline'] ? 'true' : 'false'; ?>;
        var offlineMap = { fundamentos: true, estructuras: false };
        var offline = (module && offlineMap.hasOwnProperty(module)) ? offlineMap[module] : offlineDefault;
        var forceOnline = (role === 'teacher' && module === 'estructuras');
        if (forceOnline) offline = false;
        window.AI_CONFIG = {
          endpoint: "<?php echo $cfg['endpoint']; ?>",
          token: offline ? '' : "<?php echo $cfg['token']; ?>",
          customerId: "<?php echo $cfg['customerId']; ?>",
          model: "<?php echo $cfg['model']; ?>",
          offline: offline,
          forceOnline: forceOnline
        };
      })();
    </script>
    <script src="<?php echo base_url('assets/js/smart-education.js'); ?>"></script>
<?php endif; ?>
<?php if (isset($userRole) && $userRole === 'teacher'): ?>
    <script src="<?php echo base_url('js/teacher-dashboard.js'); ?>"></script>
<?php endif; ?>
<?php if (isset($userRole) && $userRole === 'student'): ?>
    <script src="<?php echo base_url('assets/js/game-ui.js'); ?>"></script>
    <script src="<?php echo base_url('assets/js/student-dashboard.js'); ?>"></script>
<?php endif; ?>
<?php if (isset($userRole) && $userRole === 'admin'): ?>
    <script src="<?php echo base_url('assets/js/admin-dashboard.js'); ?>"></script>
<?php endif; ?>
</body>
</html>
<?php
    // Cargar CSS específico de rol solo si el archivo existe
    $roleStyleFile = __DIR__ . '/../../styles/' . (isset($userRole) ? $userRole : '') . '.css';
    if (isset($userRole) && $userRole !== '' && file_exists($roleStyleFile)) :
?>
        <link rel="stylesheet" href="<?php echo base_url('styles/' . $userRole . '.css'); ?>">
<?php endif; ?>