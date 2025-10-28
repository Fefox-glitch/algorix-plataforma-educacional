<?php
// Variables disponibles: $user
$name = isset($user['name']) ? htmlspecialchars($user['name']) : 'Estudiante';
$role = isset($user['role']) ? htmlspecialchars($user['role']) : 'student';
// Módulo seleccionado (desde dashboard)
$moduleSlug = isset($_GET['module']) ? $_GET['module'] : null;
$moduleTitle = null;
if ($moduleSlug === 'fundamentos') {
    $moduleTitle = 'Fundamentos';
} elseif ($moduleSlug === 'estructuras') {
    $moduleTitle = 'Estructuras de control';
}
?>

<div id="studentScreen" class="student-container game-content">
    <aside class="sidebar">
        <h3>Tu Navegación</h3>
        <nav class="student-nav">
            <a href="<?php echo base_url('student/game'); ?>" class="nav-item active">Modo Juego</a>
            <a href="<?php echo base_url('student/dashboard'); ?>" class="nav-item">Inicio</a>
        </nav>

        <div class="helper-assistant">
            <h4 class="sidebar-section-title">Tu Asistente</h4>
            <div class="helper-status">
                <span class="helper-pulse"></span>
                <span>Conectado al asistente educativo</span>
            </div>
            <p>Te ayudo a practicar y mejorar tus habilidades de programación.</p>
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
            <button id="createExercise" class="create-btn" onclick="createNewExercise()">
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
                ⬇️ Crea tu primer ejercicio con el botón ✨
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
        <!-- Admin Panel oculto -->
        <div id="adminPanel" class="admin-panel" style="display: none;">
            <h3>🎛️ Panel de Control Avanzado</h3>
            <div class="admin-controls">
                <button class="btn btn-friendly" onclick="showAdvancedUserManagement()">👥 Gestión de Usuarios</button>
                <button class="btn btn-friendly" onclick="showAdvancedStatistics()">📊 Analytics Avanzado</button>
                <button class="btn btn-primary" onclick="gameEngine.showGradingSystem()">📈 Sistema de Notas</button>
                <button class="btn btn-warning" onclick="showExportCenter()">📄 Centro de Exportación</button>
                <button class="btn btn-secondary" onclick="showInstitutionalSettings()">⚙️ Configuración</button>
                <button class="btn btn-warning" onclick="showAdvancedCodeGenerator()">🔑 Gestión de Códigos</button>
                <button class="btn btn-primary" onclick="showCommunicationCenter()">📢 Comunicación</button>
                <button class="btn btn-friendly" onclick="showAIAnalyticsDashboard()">🤖 Analytics IA</button>
            </div>
        </div>

        <!-- Challenge Area -->
        <div class="challenge-area">
            <div class="challenge-description">
                <?php if (!empty($moduleTitle)) { ?>
                <div class="module-banner" style="background: rgba(16, 185, 129, 0.1); padding: 10px 12px; border-radius: 8px; margin-bottom: 10px; border: 1px solid var(--border-color);">
                    <strong>Módulo seleccionado:</strong> <?php echo htmlspecialchars($moduleTitle); ?>
                </div>
                <?php } ?>
                <h2 id="challengeTitle">¡Bienvenido a tu aventura de programación! 🚀</h2>
                <p id="challengeDesc">¡Empecemos con el ejercicio de bienvenida! Está especialmente diseñado para que comiences tu viaje en la programación.</p>
                <div id="challengeInstructions">
                    <p><strong>🎮 Cómo funciona:</strong> Ejercicios paso a paso | Ayuda cuando la necesites | ¡Puntos por cada logro!</p>
                    <p><strong>Controles:</strong> F9 (Probar código) | F10 (Enviar) | Tab (Espacios)</p>
                    <div style="background: rgba(16, 185, 129, 0.1); padding: 15px; border-radius: 10px; margin-top: 15px;">
                        <strong>🎯 Tu progreso:</strong> Completa ejercicios básicos → Desbloquea ejercicios avanzados → Creador de ejercicios 
                    </div>
                </div>
            </div>

            <!-- Smart Feedback Panel -->
            <div id="smartFeedback" class="smart-feedback">
                <h4>
                    💡 Tu Ayudante Personal 
                    <div class="thinking" id="thinking" style="display: none;">
                        <span>Analizando tu código</span>
                        <div class="thinking-dots">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </h4>
                <div id="smartFeedbackContent"></div>
            </div>

            <!-- Code Section -->
            <div id="codeSection" class="code-section" style="display: none;">
                <div class="editor-toolbar">
                    <div class="editor-actions">
                        <button class="btn btn-primary" onclick="runCode()">▶️ Probar Código</button>
                        <button class="btn btn-friendly" onclick="getSmartHint()">💡 Pedir Ayuda</button>
                        <button class="btn btn-secondary" onclick="resetCode()">🔄 Empezar de Nuevo</button>
                        <button class="btn btn-success" onclick="submitSolution()">✅ ¡Terminé!</button>
                    </div>
                    <div class="editor-info">
                        <span id="codeLength">0 caracteres</span>
                        <span id="codeLines">1 línea</span>
                    </div>
                </div>

                <textarea id="codeEditor" class="code-editor" placeholder="// ¡Aquí escribes tu código!\n// No te preocupes, puedes pedir ayuda cuando quieras\n// ¡Vamos a aprender juntos!"></textarea>

                <div id="hintPanel" class="hint-panel">
                    <h4>💡 Pista para ti</h4>
                    <div style="background: rgba(16, 185, 129, 0.1); padding: 20px; border-radius: 10px;">
                        <p id="hintText">Aquí aparecerá una pista cuando la necesites.</p>
                    </div>
                </div>

                <div id="resultsPanel" class="results-panel">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                        <h4 id="resultsTitle">Resultado</h4>
                        <button onclick="document.getElementById('resultsPanel').style.display='none'" style="background: none; border: none; cursor: pointer; font-size: 20px;">✕</button>
                    </div>
                    <div id="resultsContent"></div>
                </div>
            </div>
        </div>
    </main>
</div>