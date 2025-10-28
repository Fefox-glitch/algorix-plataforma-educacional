<?php // Vista parcial: Panel de Administrador ?>

    <div class="admin-container">
        <aside class="admin-sidebar">
          <nav class="admin-nav">
            <button class="sidebar-section-toggle" data-target="menu-group">
              <span class="caret"></span> Menú
            </button>
            <div id="menu-group" class="collapse-group">
              <a class="nav-item active" data-section="overview" href="<?php echo base_url('admin/dashboard#overview'); ?>" onclick="if(window.goAdminSection){ goAdminSection('overview'); return false; }">📊 Resumen</a>
              <a class="nav-item" data-section="labs" href="<?php echo base_url('admin/dashboard#labs'); ?>" onclick="if(window.goAdminSection){ goAdminSection('labs'); return false; }">🖥️ Laboratorios</a>
              <a class="nav-item" data-section="computers" href="<?php echo base_url('admin/dashboard#computers'); ?>" onclick="if(window.goAdminSection){ goAdminSection('computers'); return false; }">💻 Computadoras</a>
              <a class="nav-item" data-section="groups" href="<?php echo base_url('admin/dashboard#groups'); ?>" onclick="if(window.goAdminSection){ goAdminSection('groups'); return false; }">👥 Grupos</a>
              <a class="nav-item" data-section="teachers" href="<?php echo base_url('admin/dashboard#teachers'); ?>" onclick="if(window.goAdminSection){ goAdminSection('teachers'); return false; }">👨‍🏫 Profesores</a>
              <a class="nav-item" data-section="actions" href="<?php echo base_url('admin/dashboard#actions'); ?>" onclick="if(window.goAdminSection){ goAdminSection('actions'); return false; }">⚡ Acciones</a>
              <a class="nav-item" data-section="sessions" href="<?php echo base_url('admin/dashboard#sessions'); ?>" onclick="if(window.goAdminSection){ goAdminSection('sessions'); return false; }">📅 Sesiones</a>
            </div>

            <button class="sidebar-section-toggle collapsed" data-target="adv-group" style="margin-top:12px;">
              <span class="caret"></span> Panel Avanzado
            </button>
            <div id="adv-group" class="collapse-group" style="display:none;">
              <a class="nav-item" data-section="panel-usuarios" href="<?php echo base_url('admin/dashboard#panel-usuarios'); ?>" onclick="if(window.goAdminSection){ goAdminSection('panel-usuarios'); return false; }">👥 Gestión de Usuarios</a>
              <a class="nav-item" data-section="panel-analytics" href="<?php echo base_url('admin/dashboard#panel-analytics'); ?>" onclick="if(window.goAdminSection){ goAdminSection('panel-analytics'); return false; }">📈 Analytics Avanzado</a>
              <a class="nav-item" data-section="panel-notas" href="<?php echo base_url('admin/dashboard#panel-notas'); ?>" onclick="if(window.goAdminSection){ goAdminSection('panel-notas'); return false; }">🧮 Sistema de Notas</a>
              <a class="nav-item" data-section="panel-exportacion" href="<?php echo base_url('admin/dashboard#panel-exportacion'); ?>" onclick="if(window.goAdminSection){ goAdminSection('panel-exportacion'); return false; }">🗂️ Centro de Exportación</a>
              <a class="nav-item" data-section="panel-codigos" href="<?php echo base_url('admin/dashboard#panel-codigos'); ?>" onclick="if(window.goAdminSection){ goAdminSection('panel-codigos'); return false; }">🧩 Gestión de Códigos</a>
              <a class="nav-item" data-section="panel-comunicacion" href="<?php echo base_url('admin/dashboard#panel-comunicacion'); ?>" onclick="if(window.goAdminSection){ goAdminSection('panel-comunicacion'); return false; }">💬 Comunicación</a>
              <a class="nav-item" data-section="panel-analytics-ia" href="<?php echo base_url('admin/dashboard#panel-analytics-ia'); ?>" onclick="if(window.goAdminSection){ goAdminSection('panel-analytics-ia'); return false; }">🤖 Analytics IA</a>
              <a class="nav-item" data-section="panel-ai-config" href="<?php echo base_url('admin/dashboard#panel-ai-config'); ?>" onclick="if(window.goAdminSection){ goAdminSection('panel-ai-config'); return false; }">🛠️ Configuración IA</a>
            </div>
          </nav>
        </aside>
        <main class="admin-content">
            <section id="advanced-control" class="advanced-control" style="display:none;">
                <div class="advanced-header">
                    <span class="dot"></span>
                    <h1>Panel de Control Avanzado</h1>
                </div>
                <div class="advanced-grid">
                    <button class="panel-card green" data-panel="usuarios" onclick="showAdminPanel('usuarios')">
                        <div class="panel-title">Gestión de Usuarios</div>
                        <div class="panel-desc">Administra estudiantes y profesores</div>
                        <div class="panel-icon">👥</div>
                    </button>

                    <button class="panel-card purple" data-panel="analytics" onclick="showAdminPanel('analytics')">
                        <div class="panel-title">Analytics Avanzado</div>
                        <div class="panel-desc">Métricas detalladas de rendimiento</div>
                        <div class="panel-icon">📈</div>
                    </button>

                    <button class="panel-card blue" data-panel="notas" onclick="showAdminPanel('notas')">
                        <div class="panel-title">Sistema de Notas</div>
                        <div class="panel-desc">Calificaciones y reportes</div>
                        <div class="panel-icon">🧮</div>
                    </button>

                    <button class="panel-card gold" data-panel="exportacion" onclick="showAdminPanel('exportacion')">
                        <div class="panel-title">Centro de Exportación</div>
                        <div class="panel-desc">CSV, PDF y más</div>
                        <div class="panel-icon">🗂️</div>
                    </button>


                    <button class="panel-card gold" data-panel="codigos" onclick="showAdminPanel('codigos')">
                        <div class="panel-title">Gestión de Códigos</div>
                        <div class="panel-desc">Ejercicios y retos</div>
                        <div class="panel-icon">🧩</div>
                    </button>

                    <button class="panel-card teal" data-panel="comunicacion" onclick="showAdminPanel('comunicacion')">
                        <div class="panel-title">Comunicación</div>
                        <div class="panel-desc">Anuncios y mensajes</div>
                        <div class="panel-icon">💬</div>
                    </button>

                    <button class="panel-card magenta" data-panel="analytics-ia" onclick="showAdminPanel('analytics-ia')">
                        <div class="panel-title">Analytics IA</div>
                        <div class="panel-desc">Insights asistidos</div>
                        <div class="panel-icon">🤖</div>
                    </button>

                    <!-- Opciones principales integradas como tarjetas en el Panel Avanzado -->
                    <button class="panel-card teal" data-go="overview" onclick="goAdminSection('overview')">
                        <div class="panel-title">Resumen General</div>
                        <div class="panel-desc">Estado general del sistema</div>
                        <div class="panel-icon">📊</div>
                    </button>

                    <button class="panel-card blue" data-go="labs" onclick="goAdminSection('labs')">
                        <div class="panel-title">Laboratorios</div>
                        <div class="panel-desc">Gestiona laboratorios</div>
                        <div class="panel-icon">🖥️</div>
                    </button>

                    <button class="panel-card green" data-go="computers" onclick="goAdminSection('computers')">
                        <div class="panel-title">Computadoras</div>
                        <div class="panel-desc">Equipos y estados</div>
                        <div class="panel-icon">💻</div>
                    </button>

                    <button class="panel-card purple" data-go="groups" onclick="goAdminSection('groups')">
                        <div class="panel-title">Grupos</div>
                        <div class="panel-desc">Agrupaciones activas</div>
                        <div class="panel-icon">👥</div>
                    </button>

                    <button class="panel-card purple" data-go="teachers" onclick="goAdminSection('teachers')">
                        <div class="panel-title">Profesores</div>
                        <div class="panel-desc">Registro y asignaciones</div>
                        <div class="panel-icon">👨‍🏫</div>
                    </button>

                    <button class="panel-card gold" data-go="actions" onclick="goAdminSection('actions')">
                        <div class="panel-title">Acciones</div>
                        <div class="panel-desc">Operaciones masivas</div>
                        <div class="panel-icon">⚡</div>
                    </button>

                    <button class="panel-card magenta" data-go="sessions" onclick="goAdminSection('sessions')">
                        <div class="panel-title">Sesiones</div>
                        <div class="panel-desc">Historial y estado</div>
                        <div class="panel-icon">📅</div>
                    </button>

                    <button class="panel-card teal" data-panel="ai-config" onclick="showAdminPanel('ai-config')">
                        <div class="panel-title">Configuración IA</div>
                        <div class="panel-desc">Token, endpoint y modelo</div>
                        <div class="panel-icon">🛠️</div>
                    </button>
                </div>
            </section>
             <!-- Secciones del Panel Avanzado (conectadas a datos del sistema) -->
<?php
  // Cargar usuarios desde storage (modo offline)
  $usersPath = __DIR__ . '/../../storage/users.json';
  $users = [];
  if (file_exists($usersPath)) {
      $json = file_get_contents($usersPath);
      $users = json_decode($json, true) ?: [];
  }
  $students = array_values(array_filter($users, function($u){ return isset($u['role']) && $u['role'] === 'student'; }));
  $teachers = array_values(array_filter($users, function($u){ return isset($u['role']) && $u['role'] === 'teacher'; }));
  $totals = ['total'=>count($users), 'students'=>count($students), 'teachers'=>count($teachers)];

  function algx_csv_users($users){
      $columns = ['id','name','email','role'];
      $csv = implode(',', $columns) . "\n";
      foreach ($users as $u) {
          $row = [];
          foreach ($columns as $c) { $row[] = isset($u[$c]) ? str_replace(["\n", "\r", ","], ' ', $u[$c]) : ''; }
          $csv .= implode(',', $row) . "\n";
      }
      return $csv;
  }
  $csvData = base64_encode(algx_csv_users($users));

  $offlineMode = isset($_ENV['OFFLINE_MODE']) && $_ENV['OFFLINE_MODE'] === 'true';
  $aiEndpoint = isset($_ENV['AI_ENDPOINT']) ? $_ENV['AI_ENDPOINT'] : 'https://oi-server.onrender.com/chat/completions';

  // logs recientes
  $logFiles = glob(__DIR__ . '/../../logs/app_*.log');
  rsort($logFiles);
  $recentLogs = [];
  if ($logFiles) {
     $lines = @file($logFiles[0], FILE_IGNORE_NEW_LINES) ?: [];
     $recentLogs = array_slice($lines, max(0, count($lines) - 5));
  }

  // Lectores y métricas de labs, computadoras, grupos y sesiones (modo offline)
  $storageDir = __DIR__ . '/../../storage';
  function algx_read_json($path) {
      if (file_exists($path)) {
          $content = file_get_contents($path);
          $data = json_decode($content, true);
          if (is_array($data)) return $data;
      }
      return [];
  }
  $labs = algx_read_json($storageDir . '/labs.json');
  $computers = algx_read_json($storageDir . '/computers.json');
  $groups = algx_read_json($storageDir . '/groups.json');
  $sessions = algx_read_json($storageDir . '/sessions.json');

  $labsCount = is_array($labs) ? count($labs) : 0;
  $computersCount = is_array($computers) ? count($computers) : 0;
  $onlineComputersCount = 0;
  foreach ($computers as $c) {
      $status = isset($c['status']) ? strtolower($c['status']) : (isset($c['estado']) ? strtolower($c['estado']) : '');
      if ($status === 'online' || $status === 'en_linea') $onlineComputersCount++;
  }
  $groupsCount = is_array($groups) ? count($groups) : 0;

  $labComputerStats = [];
  foreach ($labs as $lab) {
      $labId = isset($lab['id']) ? $lab['id'] : (isset($lab['lab_id']) ? $lab['lab_id'] : null);
      $labName = isset($lab['name']) ? $lab['name'] : (isset($lab['nombre']) ? $lab['nombre'] : ('Lab ' . $labId));
      if ($labId === null) continue;
      $labComputerStats[$labId] = ['name'=>$labName,'online'=>0,'offline'=>0,'maintenance'=>0,'error'=>0,'total'=>0];
  }
  foreach ($computers as $comp) {
      $labId = isset($comp['lab_id']) ? $comp['lab_id'] : (isset($comp['laboratorio_id']) ? $comp['laboratorio_id'] : null);
      $status = isset($comp['status']) ? strtolower($comp['status']) : (isset($comp['estado']) ? strtolower($comp['estado']) : 'offline');
      if (!isset($labComputerStats[$labId])) {
          $labComputerStats[$labId] = ['name'=>'Lab ' . $labId,'online'=>0,'offline'=>0,'maintenance'=>0,'error'=>0,'total'=>0];
      }
      $labComputerStats[$labId]['total']++;
      if (isset($labComputerStats[$labId][$status])) $labComputerStats[$labId][$status]++;
      else $labComputerStats[$labId]['offline']++;
  }

  $activeSessions = [];
  foreach ($sessions as $s) {
      $ended = isset($s['ended_at']) ? $s['ended_at'] : (isset($s['fin']) ? $s['fin'] : null);
      if ($ended === null || $ended === '') $activeSessions[] = $s;
  }
?>
<section id="panel-ai-config" class="section panel-section" style="display:none;">
  <div class="section-header"><h1>Configuración de IA</h1></div>
  <div class="cards-grid">
    <div class="card">
      <h3>Parámetros</h3>
      <div class="form-grid">
        <label>Endpoint
          <input id="ai_endpoint" type="text" placeholder="https://.../chat/completions">
        </label>
        <label>Modelo
          <input id="ai_model" type="text" placeholder="openrouter/claude-sonnet-4">
        </label>
        <label>Token
          <input id="ai_token" type="password" placeholder="sk-...">
        </label>
        <label>Customer ID
          <input id="ai_customer" type="text" placeholder="opcional">
        </label>
        <label style="display:flex;align-items:center;gap:8px;">
          <input id="ai_offline" type="checkbox">Modo Offline
        </label>
      </div>
      <div style="margin-top:12px;display:flex;gap:8px;">
        <button class="btn btn-primary" id="aiConfigSaveBtn" onclick="saveAIConfig()">Guardar</button>
        <button class="btn btn-secondary" onclick="loadAIConfig()">Recargar</button>
      </div>
      <small id="aiConfigStatus" class="muted"></small>
    </div>
    <div class="card">
      <h3>Estado actual</h3>
      <pre id="aiConfigPreview" style="background:#f7f7f8;padding:12px;border-radius:8px;overflow:auto;"></pre>
      <button class="btn btn-secondary" onclick="goAdminSection('overview')">Volver al resumen</button>
    </div>
  </div>
</section>
<section id="panel-usuarios" class="section panel-section" style="display:none;">
  <div class="section-header"><h1>Gestión de Usuarios</h1></div>
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon">👥</div>
      <div class="stat-info">
        <div class="stat-value"><?php echo $totals['total']; ?></div>
        <div class="stat-label">Usuarios totales</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">🎓</div>
      <div class="stat-info">
        <div class="stat-value"><?php echo $totals['students']; ?></div>
        <div class="stat-label">Estudiantes</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">👨‍🏫</div>
      <div class="stat-info">
        <div class="stat-value"><?php echo $totals['teachers']; ?></div>
        <div class="stat-label">Profesores</div>
      </div>
    </div>
  </div>
  <div class="cards-grid">
    <div class="card">
      <h3>Listado</h3>
      <?php if(empty($users)): ?>
        <p>No hay usuarios registrados en modo offline.</p>
      <?php else: ?>
        <table class="table">
          <thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th></tr></thead>
          <tbody>
            <?php foreach($users as $u): ?>
            <tr>
              <td><?php echo htmlspecialchars(isset($u['id']) ? $u['id'] : ''); ?></td>
              <td><?php echo htmlspecialchars(isset($u['name']) ? $u['name'] : ''); ?></td>
              <td><?php echo htmlspecialchars(isset($u['email']) ? $u['email'] : ''); ?></td>
              <td><?php echo htmlspecialchars(isset($u['role']) ? $u['role'] : ''); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</section>
<section id="panel-analytics" class="section panel-section" style="display:none;">
  <div class="section-header"><h1>Analytics Avanzado</h1></div>
  <div class="cards-grid">
    <div class="card">
      <h3>Distribución de Roles</h3>
      <p>Estudiantes: <?php echo $totals['students']; ?> | Profesores: <?php echo $totals['teachers']; ?> | Total: <?php echo $totals['total']; ?></p>
      <?php
        $ratio = $totals['total'] > 0 ? round(($totals['students'] / $totals['total']) * 100) : 0;
      ?>
      <div class="progress">
        <div class="progress-bar" style="width: <?php echo $ratio; ?>%;"></div>
      </div>
      <small><?php echo $ratio; ?>% estudiantes</small>
    </div>
    <div class="card">
      <h3>Actividad reciente</h3>
      <?php if(empty($recentLogs)): ?>
        <p>Sin eventos registrados.</p>
      <?php else: ?>
        <ul>
          <?php foreach($recentLogs as $line): ?>
            <li><?php echo htmlspecialchars($line); ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</section>
<section id="panel-notas" class="section panel-section" style="display:none;">
  <div class="section-header"><h1>Sistema de Notas</h1></div>
  <p class="section-desc">
    <?php echo $totals['students'] > 0 ? 'Aún no hay calificaciones registradas.' : 'No hay estudiantes en el sistema.'; ?>
  </p>
</section>
<section id="panel-exportacion" class="section panel-section" style="display:none;">
  <div class="section-header"><h1>Centro de Exportación</h1></div>
  <div class="cards-grid">
    <div class="card">
      <h3>Usuarios (CSV)</h3>
      <p>Exporta el listado actual en formato CSV.</p>
      <a class="btn btn-primary" download="usuarios.csv" href="data:text/csv;base64,<?php echo $csvData; ?>">Descargar CSV</a>
      <a class="btn btn-secondary" href="<?php echo base_url('storage/users.json'); ?>" target="_blank">Ver JSON</a>
    </div>
  </div>
</section>
<section id="panel-codigos" class="section panel-section" style="display:none;">
  <div class="section-header"><h1>Gestión de Códigos</h1></div>
  <?php
    $codes = [];
    try {
      for($i=0; $i<5; $i++){ $codes[] = strtoupper(bin2hex(random_bytes(3))); }
    } catch (Exception $e) { $codes = ['ABC123','DEF456','GHI789']; }
  ?>
  <div class="cards-grid">
    <div class="card">
      <h3>Códigos generados</h3>
      <ul>
        <?php foreach($codes as $c): ?><li><?php echo $c; ?></li><?php endforeach; ?>
      </ul>
      <small>Generados localmente para pruebas.</small>
    </div>
  </div>
</section>
<section id="panel-comunicacion" class="section panel-section" style="display:none;">
  <div class="section-header"><h1>Comunicación</h1></div>
  <p class="section-desc">Envío de anuncios y mensajes</p>
  <div class="card">
    <p>Soporte: <a href="mailto:soporte@algorix.edu">soporte@algorix.edu</a></p>
    <?php if(!empty($recentLogs)): ?>
      <h4>Actividad reciente</h4>
      <ul>
        <?php foreach($recentLogs as $line): ?><li><?php echo htmlspecialchars($line); ?></li><?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>
<section id="panel-analytics-ia" class="section panel-section" style="display:none;">
  <div class="section-header"><h1>Analytics IA</h1></div>
  <div class="card">
    <p>Modo Offline: <strong><?php echo $offlineMode ? 'Sí' : 'No'; ?></strong></p>
    <p>Endpoint IA: <code><?php echo htmlspecialchars($aiEndpoint); ?></code></p>
  </div>
</section>
<section id="overview" class="section active">
                <h1>Resumen General del Sistema</h1>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">🖥️</div>
                        <div class="stat-info">
                            <div class="stat-value" id="total-labs"><?php echo $labsCount; ?></div>
                            <div class="stat-label">Laboratorios</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">💻</div>
                        <div class="stat-info">
                            <div class="stat-value" id="total-computers"><?php echo $computersCount; ?></div>
                            <div class="stat-label">Computadoras</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">✅</div>
                        <div class="stat-info">
                            <div class="stat-value" id="online-computers"><?php echo $onlineComputersCount; ?></div>
                            <div class="stat-label">En Línea</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-info">
                            <div class="stat-value" id="total-groups"><?php echo $groupsCount; ?></div>
                            <div class="stat-label">Grupos Activos</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">📅</div>
                        <div class="stat-info">
                            <div class="stat-value" id="total-sessions"><?php echo count($activeSessions); ?></div>
                            <div class="stat-label">Sesiones Activas</div>
                        </div>
                    </div>
                </div>

                <div class="section-block">
                    <h3>Sesiones Activas</h3>
                    <div id="overview-sessions" class="compact-list">
                        <div class="empty-state">Sin sesiones activas</div>
                    </div>
                </div>

                <div class="section-header">
                    <h2>Estado de Computadoras por Laboratorio</h2>
                </div>
                <div id="labs-status-container" class="labs-status">
                    <?php if (empty($labComputerStats)): ?>
                      <p>No hay datos de laboratorios y computadoras en storage.</p>
                    <?php else: ?>
                      <div class="cards-grid">
                      <?php foreach ($labComputerStats as $labId => $info): ?>
                        <div class="card">
                          <h3><?php echo htmlspecialchars($info['name']); ?></h3>
                          <p>Total: <?php echo $info['total']; ?></p>
                          <div style="display:flex;gap:12px;flex-wrap:wrap;">
                            <span>✅ <?php echo $info['online']; ?></span>
                            <span>🟡 <?php echo $info['maintenance']; ?></span>
                            <span>🔴 <?php echo $info['offline']; ?></span>
                            <span>⚠️ <?php echo $info['error']; ?></span>
                          </div>
                        </div>
                      <?php endforeach; ?>
                      </div>
                    <?php endif; ?>
                </div>

                <div class="section-header">
                    <h2>Sesiones Activas</h2>
                </div>
                <div id="active-sessions-container" class="sessions-list">
                    <?php if (empty($activeSessions)): ?>
                      <p>No hay sesiones activas.</p>
                    <?php else: ?>
                      <ul>
                        <?php foreach ($activeSessions as $s): 
                          $labName = isset($s['lab_name']) ? $s['lab_name'] : (isset($s['laboratorio']) ? $s['laboratorio'] : ('Lab ' . (isset($s['lab_id'])?$s['lab_id']:'?')));
                          $groupName = isset($s['group_name']) ? $s['group_name'] : (isset($s['grupo']) ? $s['grupo'] : 'Grupo');
                          $teacherName = isset($s['teacher_name']) ? $s['teacher_name'] : (isset($s['profesor']) ? $s['profesor'] : '');
                          $started = isset($s['started_at']) ? $s['started_at'] : (isset($s['inicio']) ? $s['inicio'] : '');
                        ?>
                          <li>
                            <strong><?php echo htmlspecialchars($labName); ?></strong> — <?php echo htmlspecialchars($groupName); ?> — <?php echo htmlspecialchars($teacherName); ?>
                            <span style="opacity:.7;">Inicio: <?php echo htmlspecialchars($started); ?></span>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    <?php endif; ?>
                </div>
            </section>

            <section id="labs" class="section">
                <div class="section-header">
                    <h1>Gestión de Laboratorios</h1>
                    <button class="btn btn-primary" onclick="showCreateLabModal()">
                        + Nuevo Laboratorio
                    </button>
                </div>
                <div id="labs-container" class="cards-grid">
                </div>
            </section>

            <section id="computers" class="section">
                <div class="section-header">
                    <h1>Gestión de Computadoras</h1>
                    <button class="btn btn-primary" onclick="showCreateComputerModal()">
                        + Nueva Computadora
                    </button>
                </div>

                <div class="filters">
                    <label>
                        Filtrar por estado:
                        <select id="computer-filter" onchange="filterComputers()">
                            <option value="all">Todas</option>
                            <option value="online">En línea</option>
                            <option value="offline">Fuera de línea</option>
                            <option value="maintenance">Mantenimiento</option>
                            <option value="error">Error</option>
                        </select>
                    </label>
                </div>

                <div id="computers-container" class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Laboratorio</th>
                                <th>IP</th>
                                <th>MAC</th>
                                <th>Estado</th>
                                <th>Última Conexión</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="computers-tbody">
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="groups" class="section">
                <div class="section-header">
                    <h1>Gestión de Grupos</h1>
                    <button class="btn btn-primary" onclick="showCreateGroupModal()">
                        + Nuevo Grupo
                    </button>
                </div>
                <div id="groups-container" class="cards-grid">
                </div>
            </section>

            <section id="teachers" class="section">
                <div class="section-header">
                    <h1>Gestión de Profesores</h1>
                </div>
                <div id="teachers-container" class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Grupos Asignados</th>
                                <th>Fecha de Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="teachers-tbody">
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="actions" class="section">
                <div class="section-header">
                    <h1>Control de Acciones Masivas</h1>
                </div>

                <div class="action-controls">
                    <div class="control-group">
                        <h3>Acciones por Laboratorio</h3>
                        <label>
                            Seleccionar Laboratorio:
                            <select id="lab-action-select">
                                <option value="">Seleccione un laboratorio</option>
                            </select>
                        </label>
                        <div class="action-buttons">
                            <button class="btn btn-success" onclick="performLabAction('power_on')">
                                ⚡ Encender Todas
                            </button>
                            <button class="btn btn-warning" onclick="performLabAction('restart')">
                                🔄 Reiniciar Todas
                            </button>
                            <button class="btn btn-danger" onclick="performLabAction('power_off')">
                                🔴 Apagar Todas
                            </button>
                        </div>
                    </div>
                </div>

                <div class="section-header">
                    <h2>Historial de Acciones Recientes</h2>
                </div>
                <div id="actions-history-container" class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Computadora</th>
                                <th>Acción</th>
                                <th>Realizado por</th>
                                <th>Estado</th>
                                <th>Resultado</th>
                            </tr>
                        </thead>
                        <tbody id="actions-tbody">
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="sessions" class="section">
                <div class="section-header">
                    <h1>Historial de Sesiones</h1>
                </div>
                <div id="sessions-container" class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Laboratorio</th>
                                <th>Grupo</th>
                                <th>Profesor</th>
                                <th>Inicio</th>
                                <th>Fin</th>
                                <th>Duración</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="sessions-tbody">
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <div id="modal-overlay" class="modal-overlay" onclick="closeModal()"></div>
    <div id="modal-container" class="modal-container"></div>

            <!-- Modales de creación -->
            <div id="createLabModal" class="modal" aria-hidden="true" style="display:none">
              <div class="modal-content">
                <div class="modal-header">
                  <h2>Nuevo Laboratorio</h2>
                  <button type="button" class="modal-close" aria-label="Cerrar" onclick="(function(){var m=document.getElementById('createLabModal'); if(m){m.style.display='none'; m.setAttribute('aria-hidden','true');}})()">&times;</button>
                </div>
                <div class="modal-body">
                  <form id="createLabForm">
                    <label>Nombre
                      <input type="text" id="lab_name" required maxlength="100" />
                    </label>
                    <label>Ubicación
                      <input type="text" id="lab_location" maxlength="150" />
                    </label>
                    <label>Capacidad
                      <input type="number" id="lab_capacity" min="0" max="10000" value="0" />
                    </label>
                    <label>
                      <input type="checkbox" id="lab_active" checked /> Activo
                    </label>
                    <div class="modal-actions" style="margin-top:12px; display:flex; gap:8px; justify-content:flex-end">
                      <button type="submit" class="btn btn-primary">Crear</button>
                      <button type="button" class="btn btn-secondary" onclick="(function(){var m=document.getElementById('createLabModal'); if(m){m.style.display='none'; m.setAttribute('aria-hidden','true');}})()">Cancelar</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <div id="createComputerModal" class="modal" aria-hidden="true" style="display:none">
              <div class="modal-content">
                <div class="modal-header">
                  <h2>Nueva Computadora</h2>
                  <button type="button" class="modal-close" aria-label="Cerrar" onclick="(function(){var m=document.getElementById('createComputerModal'); if(m){m.style.display='none'; m.setAttribute('aria-hidden','true');}})()">&times;</button>
                </div>
                <div class="modal-body">
                  <form id="createComputerForm">
                    <label>Nombre
                      <input type="text" id="computer_name" required maxlength="100" />
                    </label>
                    <label>Laboratorio
                      <select id="computer_lab" required>
                        <option value="">Seleccione un laboratorio</option>
                      </select>
                    </label>
                    <label>IP
                      <input type="text" id="computer_ip" placeholder="192.168.0.10" />
                    </label>
                    <label>MAC
                      <input type="text" id="computer_mac" placeholder="AA:BB:CC:DD:EE:FF" />
                    </label>
                    <label>Estado
                      <select id="computer_status">
                        <option value="offline">offline</option>
                        <option value="online">online</option>
                        <option value="maintenance">maintenance</option>
                      </select>
                    </label>
                    <div class="modal-actions" style="margin-top:12px; display:flex; gap:8px; justify-content:flex-end">
                      <button type="submit" class="btn btn-primary">Crear</button>
                      <button type="button" class="btn btn-secondary" onclick="(function(){var m=document.getElementById('createComputerModal'); if(m){m.style.display='none'; m.setAttribute('aria-hidden','true');}})()">Cancelar</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <div id="createGroupModal" class="modal" aria-hidden="true" style="display:none">
              <div class="modal-content">
                <div class="modal-header">
                  <h2>Nuevo Grupo</h2>
                  <button type="button" class="modal-close" aria-label="Cerrar" onclick="(function(){var m=document.getElementById('createGroupModal'); if(m){m.style.display='none'; m.setAttribute('aria-hidden','true');}})()">&times;</button>
                </div>
                <div class="modal-body">
                  <form id="createGroupForm">
                    <label>Nombre
                      <input type="text" id="group_name" required maxlength="100" />
                    </label>
                    <label>Profesor
                      <select id="group_teacher" required>
                        <option value="">Seleccione un profesor</option>
                      </select>
                    </label>
                    <label>Laboratorio
                      <select id="group_lab" required>
                        <option value="">Seleccione un laboratorio</option>
                      </select>
                    </label>
                    <label>
                      <input type="checkbox" id="group_active" checked /> Activo
                    </label>
                    <div class="modal-actions" style="margin-top:12px; display:flex; gap:8px; justify-content:flex-end">
                      <button type="submit" class="btn btn-primary">Crear</button>
                      <button type="button" class="btn btn-secondary" onclick="(function(){var m=document.getElementById('createGroupModal'); if(m){m.style.display='none'; m.setAttribute('aria-hidden','true');}})()">Cancelar</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
