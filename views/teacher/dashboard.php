<?php // Vista parcial: Panel de Profesor ?>
<link rel="stylesheet" href="/styles/soft-ui-dashboard.css" />
<link rel="stylesheet" href="/styles/perfect-scrollbar.min.css" />
<!-- Hero banner movido al header global -->
<div class="teacher-container">
        <aside class="teacher-sidebar sidenav">
            <div class="dashboard-card" style="background: var(--bg-primary); border: 2px solid var(--border-color); border-radius: var(--radius); padding: 20px;">
                <h3 style="margin-bottom:10px">Panel</h3>
                <nav class="teacher-nav" style="display:flex; flex-direction: column; gap:8px;">
                    <a href="#" class="nav-item active" data-section="main-panel">🏠 Panel</a>
                    <a href="#" class="nav-item" data-section="assignments">📋 Asignaciones</a>
                    <a href="#" class="nav-item" data-section="lab-control">🧪 Laboratorios</a>
                    <a href="#" class="nav-item" data-section="users">👥 Usuarios</a>
                    <a href="#" class="nav-item" data-section="sessions">🕒 Sesiones</a>
                    <a href="#" class="nav-item" data-section="analytics">📊 Analítica</a>
                    <a href="#" class="nav-item" data-section="grades">📈 Calificaciones</a>
                    <a href="#" class="nav-item" data-section="export">📦 Exportaciones</a>
                    <a href="#" class="nav-item" data-section="code-management">🧩 Código</a>
                    <a href="#" class="nav-item" data-section="communications">📣 Comunicación</a>
                </nav>
            </div>
        </aside>

        <main class="teacher-content main-content">
            <section id="main-panel" class="section active">
                <div class="section-header">
                    <h1>Panel Principal</h1>
                </div>

                <div class="soft-toolbar">
                    <input type="search" class="soft-search" placeholder="Buscar…" />
                    <div class="soft-actions">
                        <button class="btn btn-secondary">Guía</button>
                        <button class="btn btn-primary">Acción rápida</button>
                    </div>
                </div>

                <div class="overview-grid">
                    <div class="soft-card accent-orange">
                        <div class="soft-card-title">Estudiantes activos</div>
                        <div id="overview-students" class="soft-card-value">—</div>
                    </div>
                    <div class="soft-card accent-blue">
                        <div class="soft-card-title">Sesiones</div>
                        <div id="overview-sessions" class="soft-card-value">—</div>
                    </div>
                    <div class="soft-card accent-dark">
                        <div class="soft-card-title">Cursos</div>
                        <div id="overview-courses" class="soft-card-value">—</div>
                    </div>
                    <div class="soft-card accent-purple">
                        <div class="soft-card-title">Módulos</div>
                        <div id="overview-modules" class="soft-card-value">—</div>
                    </div>
                </div>

                <div class="panels-grid">
                    <div class="panel-soft">
                        <div class="panel-title">Estado de computadoras</div>
                        <div class="progress-row">
                            <div class="progress-label">Online</div>
                            <div class="progress-bar">
                                <div id="comp-progress-fill" class="progress-fill" style="width:0%"></div>
                            </div>
                            <div id="comp-progress-label" class="progress-percent">0%</div>
                        </div>
                        <div class="progress-stats">
                            <span>Online: <strong id="comp-online">—</strong></span>
                            <span>Total: <strong id="comp-total">—</strong></span>
                        </div>
                    </div>

                    <div class="panel-soft">
                        <div class="panel-title">Proyectos</div>
                        <ul id="projects-list" class="list-soft"></ul>
                    </div>

                    <div class="panel-soft">
                        <div class="panel-title">Actividad reciente</div>
                        <ul id="activity-list" class="list-soft"></ul>
                    </div>
                </div>

                <!-- Panel de Control Avanzado removido: las opciones ahora están sólo en el menú lateral -->
            </section>

            <section id="my-groups" class="section">
                <div class="section-header">
                    <h1>Mis Grupos</h1>
                </div>

                <div id="groups-container" class="groups-grid">
                </div>

                <div id="group-details" class="group-details" style="display: none;">
                    <div class="section-header">
                        <h2 id="group-details-title"></h2>
                        <button class="btn btn-secondary" onclick="closeGroupDetails()">Volver</button>
                    </div>

                    <div class="group-info-card">
                        <div class="info-row">
                            <span class="label">Laboratorio:</span>
                            <span id="group-lab-name"></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Estudiantes:</span>
                            <span id="group-member-count"></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Estado:</span>
                            <span id="group-status"></span>
                        </div>
                    </div>

                    <h3>Miembros del Grupo</h3>
                    <div id="group-members-container" class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Fecha de Ingreso</th>
                                    <th>Computadora Asignada</th>
                                </tr>
                            </thead>
                            <tbody id="group-members-tbody">
                            </tbody>
                        </table>
                    </div>

                    <div class="section-header">
                        <h3>Computadoras Asignadas al Grupo</h3>
                        <button class="btn btn-primary" onclick="showAssignComputerModal()">
                            + Asignar Computadora
                        </button>
                    </div>
                    <div id="group-computers-container" class="computers-grid">
                    </div>
                </div>
            </section>

            <section id="lab-control" class="section">
                <div class="section-header">
                    <h1>Control de Laboratorio</h1>
                </div>

                <div class="lab-selector">
                    <label>
                        Seleccionar Laboratorio:
                        <select id="teacher-lab-select" onchange="loadLabComputers()">
                            <option value="">Seleccione un laboratorio</option>
                        </select>
                    </label>
                    <button class="btn btn-primary" onclick="startLabSession()">
                        Iniciar Sesión
                    </button>
                </div>

                <div id="lab-status-container" class="lab-status">
                </div>

                <div class="control-panel">
                    <h3>Control de Computadoras</h3>
                    <div class="action-buttons">
                        <button class="btn btn-success" onclick="performGroupAction('power_on')">
                            ⚡ Encender Seleccionadas
                        </button>
                        <button class="btn btn-warning" onclick="performGroupAction('restart')">
                            🔄 Reiniciar Seleccionadas
                        </button>
                        <button class="btn btn-secondary" onclick="performGroupAction('lock')">
                            🔒 Bloquear Seleccionadas
                        </button>
                        <button class="btn btn-danger" onclick="performGroupAction('power_off')">
                            🔴 Apagar Seleccionadas
                        </button>
                    </div>
                </div>

                <div id="lab-computers-container" class="computers-grid">
                </div>
            </section>

            <section id="assignments" class="section">
                <div class="section-header">
                    <h1>Asignaciones de Computadoras</h1>
                </div>

                <div class="filters">
                    <label>
                        Filtrar por grupo:
                        <select id="assignment-group-filter" onchange="filterAssignments()">
                            <option value="all">Todos los grupos</option>
                        </select>
                    </label>
                </div>

                <div id="assignments-container" class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Computadora</th>
                                <th>Asignado a</th>
                                <th>Grupo</th>
                                <th>Fecha de Asignación</th>
                                <th>Expira</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="assignments-tbody">
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="users" class="section">
                <div class="section" id="users-section">
                  <h2>Gestión de Usuarios (Estudiantes)</h2>
                  <div class="filters">
                    <input type="text" id="filter-q" placeholder="Buscar por nombre o email" />
                    <select id="filter-role">
                      <option value="student" selected>Solo estudiantes</option>
                      <option value="all">Todos</option>
                    </select>
                    <select id="filter-group">
                      <option value="">Todos los grupos</option>
                    </select>
                    <select id="filter-estado">
                      <option value="">Todos</option>
                      <option value="with_group">Con grupo</option>
                      <option value="without_group">Sin grupo</option>
                    </select>
                    <button id="apply-filters">Aplicar</button>
                  </div>
                  <table id="users-table">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                  </table>
                </div>
                <p style="opacity:.8;">Lista básica de estudiantes desde datos locales. (Integración con Supabase en siguiente paso).</p>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                            </tr>
                        </thead>
                        <tbody id="students-tbody"></tbody>
                    </table>
                </div>
                <div style="margin-top:10px; display:flex; gap:8px;">
                    <button class="btn btn-primary" id="btn-load-students">Cargar estudiantes</button>
                </div>
            </section>

            <section id="analytics" class="section">
                <div class="section-header">
                    <h1>Analytics Avanzadas</h1>
                </div>
                <p style="opacity:.8;">Métricas en tiempo real (Supabase si está disponible).</p>
                <div class="stats-grid">
                    <div class="stat-card"><div class="stat-icon">🎓</div><div class="stat-info"><div class="stat-value" id="metric-students">—</div><div class="stat-label">Estudiantes</div></div></div>
                    <div class="stat-card"><div class="stat-icon">👨‍🏫</div><div class="stat-info"><div class="stat-value" id="metric-teachers">—</div><div class="stat-label">Profesores</div></div></div>
                    <div class="stat-card"><div class="stat-icon">🕒</div><div class="stat-info"><div class="stat-value" id="metric-sessions">—</div><div class="stat-label">Sesiones</div></div></div>
                    <div class="stat-card"><div class="stat-icon">📚</div><div class="stat-info"><div class="stat-value" id="metric-courses">—</div><div class="stat-label">Cursos</div></div></div>
                    <div class="stat-card"><div class="stat-icon">📦</div><div class="stat-info"><div class="stat-value" id="metric-modules">—</div><div class="stat-label">Módulos</div></div></div>
                    <div class="stat-card"><div class="stat-icon">🧩</div><div class="stat-info"><div class="stat-value" id="metric-exercises">—</div><div class="stat-label">Ejercicios</div></div></div>
                    <div class="stat-card"><div class="stat-icon">📤</div><div class="stat-info"><div class="stat-value" id="metric-submissions">—</div><div class="stat-label">Submissions</div></div></div>
                    <div class="stat-card"><div class="stat-icon">⭐</div><div class="stat-info"><div class="stat-value" id="metric-grades-avg">—</div><div class="stat-label">Promedio</div></div></div>
                    <div class="stat-card"><div class="stat-icon">🧪</div><div class="stat-info"><div class="stat-value" id="metric-labs">—</div><div class="stat-label">Labs</div></div></div>
                    <div class="stat-card"><div class="stat-icon">💻</div><div class="stat-info"><div class="stat-value" id="metric-computers-online">—</div><div class="stat-label">Computadoras Online</div></div></div>
                    <div class="stat-card"><div class="stat-icon">💻</div><div class="stat-info"><div class="stat-value" id="metric-computers-total">—</div><div class="stat-label">Computadoras Totales</div></div></div>
                </div>
            </section>

            <section id="grades" class="section">
                <div class="section-header">
                    <h1>Sistema de Notas</h1>
                </div>
                <div class="filters">
                    <label>Curso:
                        <select id="grade-course-select"><option value="">Seleccione...</option></select>
                    </label>
                    <label>Ejercicio:
                        <select id="grade-exercise-select"><option value="">Todos</option></select>
                    </label>
                    <button class="btn btn-primary" id="btn-load-grades">Cargar Notas</button>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Alumno</th>
                                <th>Elemento</th>
                                <th>Calificación</th>
                                <th>Actualizado</th>
                            </tr>
                        </thead>
                        <tbody id="grades-tbody"></tbody>
                    </table>
                </div>
                <div class="cards-grid" style="margin-top:10px;">
                    <div class="stats-grid">
                        <div class="stat-card"><div class="stat-icon">⭐</div><div class="stat-info"><div class="stat-value" id="grade-avg">—</div><div class="stat-label">Promedio</div></div></div>
                        <div class="stat-card"><div class="stat-icon">⬇️</div><div class="stat-info"><div class="stat-value" id="grade-min">—</div><div class="stat-label">Mínimo</div></div></div>
                        <div class="stat-card"><div class="stat-icon">⬆️</div><div class="stat-info"><div class="stat-value" id="grade-max">—</div><div class="stat-label">Máximo</div></div></div>
                        <div class="stat-card"><div class="stat-icon">🧮</div><div class="stat-info"><div class="stat-value" id="grade-count">—</div><div class="stat-label">Registros</div></div></div>
                    </div>
                </div>
                <hr style="margin:16px 0; opacity:.5;" />
                <div class="section-header"><h2>Gestión Rápida de Notas (CRUD)</h2></div>
                <div class="filters" style="gap:8px; flex-wrap:wrap;">
                    <input id="grade-crud-id" type="number" placeholder="ID nota (para actualizar/eliminar)" style="min-width:220px;" />
                    <input id="grade-crud-user" type="text" placeholder="ID alumno" style="min-width:160px;" />
                    <input id="grade-crud-module" type="text" placeholder="ID módulo (opcional)" style="min-width:160px;" />
                    <input id="grade-crud-final" type="number" step="0.01" placeholder="Calificación final" style="min-width:160px;" />
                    <button class="btn btn-success" id="btn-grade-create">Crear</button>
                    <button class="btn btn-warning" id="btn-grade-update">Actualizar</button>
                    <button class="btn btn-danger" id="btn-grade-delete">Eliminar</button>
                    <small style="opacity:.7;">Usa el curso seleccionado arriba. IDs pueden copiarse desde la tabla.</small>
                </div>
            </section>

            <section id="export" class="section">
                <div class="section-header">
                    <h1>Centro de Exportación</h1>
                </div>
                <p style="opacity:.8;">Exporta datos visibles a CSV o PDF.</p>
                <div style="display:flex; gap:8px;">
                    <button class="btn btn-secondary" id="btn-export-assignments">Exportar Asignaciones (CSV)</button>
                    <button class="btn btn-secondary" id="btn-export-sessions">Exportar Historial (CSV)</button>
                    <button class="btn btn-primary" id="btn-export-pdf">Exportar Analytics (PDF)</button>
                </div>
            </section>

            <section id="code-management" class="section">
                <div class="section-header">
                    <h1>Gestión de Códigos</h1>
                </div>

                <div class="dashboard-card" style="background: var(--bg-primary); border: 2px solid var(--border-color); border-radius: var(--radius); padding: 20px;">
                    <h3>Crear Ejercicio Nuevo</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <label>Lenguaje
                            <select id="teacher-lang">
                                <option value="javascript">JavaScript</option>
                                <option value="python">Python</option>
                                <option value="java">Java</option>
                                <option value="html-css">HTML/CSS</option>
                            </select>
                        </label>
                        <label>Dificultad
                            <select id="teacher-diff">
                                <option value="easy">Easy</option>
                                <option value="medium">Medium</option>
                                <option value="hard">Hard</option>
                            </select>
                        </label>
                    </div>
                    <div style="margin-top: 10px;">
                        <textarea id="teacher-code" rows="6" placeholder="Pega código para evaluar o generar pista..." style="width: 100%;"></textarea>
                    </div>
                    <div style="display:flex; gap: 10px; margin-top: 10px;">
                        <button class="btn btn-primary" id="btn-gen-challenge">Generar Ejercicio</button>
                        <button class="btn btn-secondary" id="btn-gen-hint">Generar Pista</button>
                        <button class="btn btn-success" id="btn-evaluate">Evaluar Código</button>
                    </div>
                </div>
            </section>

            <section id="communications" class="section">
                <div class="section-header">
                    <h1>Comunicación</h1>
                </div>
                <div class="dashboard-card" style="background: var(--bg-primary); border: 2px solid var(--border-color); border-radius: var(--radius); padding: 20px;">
                    <h3>Enviar anuncio</h3>
                    <textarea id="comm-message" rows="4" placeholder="Escribe un anuncio para tus estudiantes..." style="width:100%;"></textarea>
                    <div style="margin-top:10px; display:flex; gap:8px;">
                        <button class="btn btn-primary" id="btn-comm-send">Publicar</button>
                        <button class="btn" id="btn-comm-clear">Limpiar</button>
                    </div>
                    <h4 style="margin-top:12px;">Anuncios publicados</h4>
                    <ul id="comm-list" style="list-style:disc; padding-left:20px;"></ul>
                </div>
            </section>

            <section id="sessions" class="section">
                <div class="section-header">
                    <h1>Historial de Sesiones</h1>
                </div>

                <div id="teacher-sessions-container" class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Laboratorio</th>
                                <th>Grupo</th>
                                <th>Inicio</th>
                                <th>Fin</th>
                                <th>Duración</th>
                                <th>Notas</th>
                            </tr>
                        </thead>
                        <tbody id="teacher-sessions-tbody">
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <div id="modal-overlay" class="modal-overlay" onclick="closeModal()"></div>
    <div id="modal-container" class="modal-container"></div>



    <script src="/js/teacher-dashboard.js"></script>
    <script defer src="/soft-ui-shim.js"></script>
    <script defer src="/perfect-scrollbar.min.js"></script>
    <script defer src="/soft-ui-dashboard.js"></script>
