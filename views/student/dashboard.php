<?php
// Variables disponibles: $user
$name = isset($user['name']) ? htmlspecialchars($user['name']) : 'Estudiante';
$role = isset($user['role']) ? htmlspecialchars($user['role']) : 'student';
?>
<!-- Hero banner movido al header global -->

<div id="studentScreen" class="student-container game-content">
    <!-- game-header interno eliminado: usamos el header global -->
    <aside class="sidebar">
        <h3>Tu Navegación</h3>
        <nav class="student-nav">
        <a href="<?php echo base_url('student/dashboard'); ?>" class="nav-item active" data-section="home">Inicio</a>
        <a href="<?php echo base_url('student/dashboard#progress'); ?>" class="nav-item" data-section="progress">Mi Progreso</a>
        <a href="<?php echo base_url('student/dashboard#basic-modules'); ?>" class="nav-item" data-section="basic-modules">Módulos Básicos</a>
        <a href="<?php echo base_url('student/game'); ?>" class="nav-item" data-section="exercises">Mis Ejercicios</a>
        <a href="<?php echo base_url('student/dashboard#messages'); ?>" class="nav-item" data-section="messages">Mensajes</a>
    </nav>
        <div class="helper-assistant">
            <h4 class="sidebar-section-title">Tu Asistente</h4>
            <div class="helper-status">
                <span class="helper-pulse"></span>
                <span>Conectado al asistente educativo</span>
            </div>
            <p>Te ayudo a practicar y mejorar tus habilidades de programación.</p>
            <button class="create-btn" disabled>
                Crear ejercicio aleatorio (prototipo)
            </button>
        </div>

        <div id="challengeCreator" class="challenge-creator locked">
            <h4 style="font-size: 16px; margin-bottom: 15px; color: var(--friendly-color);">🎯 Crear Ejercicio Nuevo</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                <select id="exerciseLanguage" class="form-input" style="font-size: 14px;">
                    <option value="javascript">JavaScript</option>
                    <option value="python">Python</option>
                    <option value="html-css">HTML/CSS</option>
                    <option value="java">Java</option>
                </select>
                <select id="exerciseDifficulty" class="form-input" style="font-size: 14px;">
                    <option value="easy">Fácil</option>
                    <option value="medium">Normal</option>
                    <option value="hard">Difícil</option>
                </select>
            </div>
            <button id="createExercise" class="create-btn" disabled>
                ✨ Crear Ejercicio
            </button>
            <div id="unlockMessage" style="display: none; font-size: 12px; text-align: center; margin-top: 10px; color: var(--friendly-color); background: rgba(16, 185, 129, 0.1); padding: 8px; border-radius: 8px;">
                ¡Ya puedes crear ejercicios! 🎉
            </div>
        </div>

        <div id="welcomeCard" class="welcome-card">
            <h3>¡Bienvenido! 👋</h3>
            <p>¡Empecemos con el ejercicio de bienvenida! Está especialmente diseñado para que empieces tu aventura de programación.</p>
            <div style="margin-top: 15px; font-size: 12px; color: var(--friendly-color);">
                ⬇️ ¡Busca "Bienvenida" en la lista de ejercicios!
            </div>
        </div>

        <h3>Logros</h3>
        <div class="achievements">
            <span class="achievement">Primer reto completado</span>
            <span class="achievement">Explorador de módulos</span>
            <span class="achievement">Bucles básicos</span>
        </div>

        <div class="progress-path">
            <div class="path-steps">
                <div class="path-step">
                    <div class="step-circle completed">1</div>
                    <div class="step-label completed">Inicio</div>
                </div>
                <div class="path-step">
                    <div class="step-circle current">2</div>
                    <div class="step-label current">Bases</div>
                </div>
                <div class="path-step">
                    <div class="step-circle">3</div>
                    <div class="step-label">Estructuras</div>
                </div>
                <div class="path-step">
                    <div class="step-circle">4</div>
                    <div class="step-label">Proyectos</div>
                </div>
            </div>
            <div class="next-goal">Siguiente objetivo: practicar condicionales y bucles.</div>
        </div>
    </aside>

    <main class="content">
        <section id="home-section" class="section" style="margin-top: 24px;">
            <div class="dashboard-header">
                <h2>Bienvenido a Algorix, <?php echo $name; ?></h2>
                <p>Este es tu entorno para aprender programación. Explora tus módulos, practica ejercicios y revisa tu progreso.</p>
            </div>
            <div class="welcome-card" style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius); padding: 16px;">
                <p>Consejo: Usa "Mis Ejercicios" para practicar y "Mi Progreso" para ver estadísticas y recomendaciones.</p>
            </div>
        </section>
        <section id="progress-section" class="section" style="margin-top: 24px;">
            <div class="dashboard-header">
                <h2>Hola, <?php echo $name; ?></h2>
                <p>Este es tu panel de estudiante. Revisa tu progreso y recomendaciones.</p>
            </div>

            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h3>Estadísticas rápidas</h3>
                    <div class="quick-stats">
                        <div class="stat-item">
                            <span class="stat-value">3</span>
                            <span class="stat-label">Retos</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value">12</span>
                            <span class="stat-label">Puntos</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value">1</span>
                            <span class="stat-label">Módulo</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value">Básico</span>
                            <span class="stat-label">Nivel</span>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card">
                    <h3>Notificaciones</h3>
                    <div class="notification-item">
                        <span class="notification-icon">📣</span>
                        <span class="notification-message">Nueva recomendación disponible para practicar bucles.</span>
                    </div>
                    <div class="notification-item">
                        <span class="notification-icon">✅</span>
                        <span class="notification-message">Has completado el reto "Imprimir números pares".</span>
                    </div>
                </div>

                <div class="dashboard-card">
                    <h3>Recomendaciones</h3>
                    <div class="recommendation-item">
                        <span class="recommendation-icon">✨</span>
                        <span class="recommendation-text">Practica condicionales con ejercicios de decisión.</span>
                    </div>
                    <div class="recommendation-item">
                        <span class="recommendation-icon">💡</span>
                        <span class="recommendation-text">Explora el módulo: Fundamentos de programación.</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Nueva sección independiente para Módulos Básicos -->
        <section id="basic-modules-section" class="section" style="margin-top: 24px;">
            <div id="basic-modules" class="modules-section">
                <div class="modules-header">
                    <h3>Módulos Básicos</h3>
                    <div><span class="badge">Prototipo</span></div>
                </div>
                <div class="modules-grid">
                    <div class="module-card unlocked">
                        <div class="module-icon">📘</div>
                        <div class="module-info">
                            <h4>Fundamentos</h4>
                            <p>Variables, tipos de datos y operadores</p>
                        </div>
                        <div class="module-status">Acceso activo</div>
                        <a href="<?php echo base_url('student/game?module=fundamentos'); ?>" class="btn btn-primary">Entrar</a>
                    </div>
                    <div class="module-card unlocked">
                        <div class="module-icon">📘</div>
                        <div class="module-info">
                            <h4>Estructuras de control</h4>
                            <p>If/else, bucles for y while</p>
                        </div>
                        <div class="module-status">Acceso activo</div>
                        <a href="<?php echo base_url('student/game?module=estructuras'); ?>" class="btn btn-primary">Entrar</a>
                    </div>
                </div>

                <!-- Material didáctico por módulo -->
                <div class="modules-learning" style="margin-top: 16px; display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="learning-card" style="border: 1px solid var(--border-color); border-radius: var(--radius); padding: 12px;">
                        <h4>Fundamentos: ¿Qué aprenderás?</h4>
                        <ul style="margin-left: 16px;">
                            <li>Variables y tipos de datos</li>
                            <li>Operadores aritméticos y de comparación</li>
                            <li>Entrada y salida básica</li>
                        </ul>
                        <div style="background: rgba(16, 185, 129, 0.08); padding: 8px; border-radius: 8px; margin-top: 8px;">
                            <strong>Ejemplo rápido (JS):</strong>
                            <pre style="white-space: pre-wrap; margin-top: 6px;">let nombre = "Ana";
let edad = 15;
console.log(`Hola ${nombre}, tienes ${edad} años`);</pre>
                        </div>
                        <div style="margin-top: 8px; font-size: 12px; color: var(--friendly-color);">Consejo: Cambia valores y observa el resultado.</div>
                    </div>
                    <div class="learning-card" style="border: 1px solid var(--border-color); border-radius: var(--radius); padding: 12px;">
                        <h4>Estructuras de control: ¿Qué aprenderás?</h4>
                        <ul style="margin-left: 16px;">
                            <li>Condicionales if/else</li>
                            <li>Bucles for y while</li>
                            <li>Control de flujo y lógica</li>
                        </ul>
                        <div style="background: rgba(16, 185, 129, 0.08); padding: 8px; border-radius: 8px; margin-top: 8px;">
                            <strong>Ejemplo rápido (JS):</strong>
                            <pre style="white-space: pre-wrap; margin-top: 6px;">for (let i=1; i<=10; i++) {
  if (i % 2 === 0) {
    console.log(i);
  }
}</pre>
                        </div>
                        <div style="margin-top: 8px; font-size: 12px; color: var(--friendly-color);">Consejo: Cambia el rango y la condición.</div>
                    </div>
                </div>
            </div>
        </section>

        <section id="exercises-section" class="section exercises-section" style="margin-top: 24px;">
            <div class="exercises-header">
                <h3>Ejercicios interactivos</h3>
                <div class="exercise-filters"><span class="badge">Prototipo</span></div>
            </div>
            <div class="exercises-grid">
                <div class="exercise-card">
                    <span class="exercise-difficulty easy">Fácil</span>
                    <div class="exercise-info">
                        <h4>Imprimir números pares</h4>
                        <p>Usa un bucle para imprimir los pares del 1 al 20.</p>
                    </div>
                    <div class="exercise-actions">
                        <button class="btn btn-primary" disabled>Empezar</button>
                    </div>
                </div>
                <div class="exercise-card">
                    <span class="exercise-difficulty medium">Media</span>
                    <div class="exercise-info">
                        <h4>Contar vocales</h4>
                        <p>Escribe una función que cuente las vocales en un texto.</p>
                    </div>
                    <div class="exercise-actions">
                        <button class="btn btn-secondary" disabled>Empezar</button>
                    </div>
                </div>
            </div>
        </section>

        <section id="progress-tracking-section" class="section progress-section" style="margin-top: 24px;">
            <div class="progress-header">
                <h3>Seguimiento de progreso</h3>
            </div>
            <div class="progress-grid">
                <div class="progress-card">
                    <h4>Logros recientes</h4>
                    <div class="achievement-item">
                        <span class="achievement-icon">🏅</span>
                        <div class="achievement-info">
                            <span class="achievement-name">Primer reto completado</span>
                            <span class="achievement-date">Hoy</span>
                        </div>
                    </div>
                    <div class="achievement-item">
                        <span class="achievement-icon">🎯</span>
                        <div class="achievement-info">
                            <span class="achievement-name">Bucles básicos</span>
                            <span class="achievement-date">Ayer</span>
                        </div>
                    </div>
                </div>
                <div class="progress-card">
                    <h4>Insignias</h4>
                    <div class="badge-item common">
                        <span class="badge-icon">⭐</span>
                        <span class="badge-name">Explorador</span>
                    </div>
                    <div class="badge-item rare">
                        <span class="badge-icon">🔥</span>
                        <span class="badge-name">Sesión constante</span>
                    </div>
                </div>
            </div>
        </section>

        <section id="feedback-section" class="section feedback-section" style="margin-top: 24px;">
            <div class="feedback-header">
                <h3>Feedback y soporte</h3>
            </div>
            <div class="feedback-grid">
                <div class="feedback-card">
                    <h4>Comentarios de profesores</h4>
                    <div class="comment-item">
                        <div class="comment-header">
                            <span class="comment-teacher">Prof. García</span>
                            <span class="comment-date">Hace 2 días</span>
                        </div>
                        <div class="comment-message">Buen trabajo en los ejercicios de bucles. Sigue así.</div>
                    </div>
                </div>
                <div class="feedback-card">
                    <h4>Feedback del sistema</h4>
                    <div class="feedback-item">
                        <span class="feedback-type AI">AI</span>
                        <div class="feedback-info">
                            <span class="feedback-message">Te recomendamos practicar condicionales antes de avanzar.</span>
                            <span class="feedback-date">Hoy</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="messages-section" class="section" style="margin-top: 24px; display: none;">
            <div class="feedback-header">
                <h3>Mensajes</h3>
                <div class="exercise-filters"><span class="badge">Asistente IA</span></div>
            </div>
            <div id="studentChat" class="chat-panel" style="background: var(--bg-secondary); border: 1.5px solid var(--border-color); border-radius: var(--radius); padding: 16px;">
                <div id="chatMessages" class="chat-messages" style="min-height: 200px; max-height: 50vh; overflow-y:auto; display:flex; flex-direction:column; gap:12px;"></div>
                <div class="chat-input-bar" style="display:flex; gap:12px; align-items:center; margin-top:12px;">
                    <textarea id="chatInput" class="form-textarea" placeholder="Escribe tu pregunta o duda..."></textarea>
                    <button id="sendChat" class="btn btn-primary" type="button">Enviar</button>
                </div>
            </div>
        </section>

        <section id="profile-section" class="section profile-section" style="margin-top: 24px;">
            <div class="profile-header">
                <h3>Perfil del estudiante</h3>
            </div>
            <div class="profile-grid">
                <div class="profile-card">
                    <h4>Información</h4>
                    <div class="info-item">
                        <span class="info-label">Nombre</span>
                        <span class="info-value"><?php echo $name; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Rol</span>
                        <span class="info-value"><?php echo strtoupper($role); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Progreso</span>
                        <span class="info-value">Básico</span>
                    </div>
                </div>
                <div class="profile-card">
                    <h4>Resumen</h4>
                    <div class="summary-item">
                        <span class="summary-label">Retos completados</span>
                        <span class="summary-value">3</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Puntos totales</span>
                        <span class="summary-value">12</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Módulos activos</span>
                        <span class="summary-value">1</span>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>