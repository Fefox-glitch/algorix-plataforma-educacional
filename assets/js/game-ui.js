// Game UI Engine for Student Game Page
(function(){
  const gameEngine = {
    edu: null,
    state: { currentChallenge: null, language: 'javascript', difficulty: 'easy', lastError: null, lastLogs: [] },
    init(){
      // Initialize SmartEducationSystem
      try {
        const hasReal = typeof window.SmartEducationSystem !== 'undefined';
        const cfg = window.AI_CONFIG || {};
        
        // Verificar si tenemos configuración válida para IA
        const hasValidConfig = cfg.endpoint && (cfg.token || cfg.offline || cfg.forceOnline);
        
        if (hasReal && hasValidConfig) {
          this.edu = new window.SmartEducationSystem(cfg);
          console.log('Sistema de IA inicializado correctamente');
        } else {
          console.warn('Configuración de IA incompleta, usando sistema mock');
          this.edu = this.createMockEdu();
        }
      } catch (e){
        console.warn('SmartEducationSystem init failed, using mock:', e);
        this.edu = this.createMockEdu();
      }

      // Expose controls
      window.createNewExercise = ()=> gameEngine.createNewExercise();
      window.runCode = ()=> gameEngine.runCode();
      window.getSmartHint = ()=> gameEngine.getSmartHint();
      window.resetCode = ()=> gameEngine.resetCode();
      window.submitSolution = ()=> gameEngine.submitSolution();

      // Concepts panel setup
      this.injectConceptsPanel();

      // --- Unlock creator in game page and auto-load a welcome exercise ---
      const creator = document.getElementById('challengeCreator');
      const createBtn = document.getElementById('createExercise');
      const unlockMsg = document.getElementById('unlockMessage');
      const codeSection = document.getElementById('codeSection');
      // If we are on the game page, enable the creator and show message
      if (creator) {
        creator.classList.remove('locked');
      }
      if (createBtn) {
        createBtn.disabled = false;
        createBtn.classList.remove('loading');
      }
      if (unlockMsg) {
        unlockMsg.style.display = 'block';
      }
      // Auto-generate a first exercise so the panel appears without clicking
      if (codeSection) {
        try {
          setTimeout(() => { this.createNewExercise(); }, 250);
        } catch(_) {}
      }
    },

    // Mock education system for offline or missing credentials
    createMockEdu(){
      const mock = {
        createExercise({language='javascript', difficulty='easy', module='fundamentos'}){
          const exercises = {
            fundamentos: {
              easy: [
                { title: 'Variables y saludo', prompt: 'Declara variables nombre y edad, imprime un saludo personalizado.', starterCode: 'let nombre = "Ana";\nlet edad = 15;\n// TODO: imprimir "Hola Ana, tienes 15 años"', tests: ['Debe imprimir "Hola Ana, tienes 15 años"'] },
                { title: 'Suma básica', prompt: 'Crea una función que sume dos números.', starterCode: 'function sumar(a, b) {\n  // TODO: retornar la suma\n}', tests: ['sumar(2, 3) debe retornar 5'] },
                { title: 'Comparación', prompt: 'Compara dos números y muestra cuál es mayor.', starterCode: 'let num1 = 10;\nlet num2 = 5;\n// TODO: mostrar cuál es mayor', tests: ['Debe mostrar que 10 es mayor que 5'] }
              ],
              medium: [
                { title: 'Promedio de números', prompt: 'Calcula el promedio de tres números.', starterCode: 'function promedio(a, b, c) {\n  // TODO: calcular y retornar el promedio\n}', tests: ['promedio(3, 4, 5) === 4'] },
                { title: 'Número par o impar', prompt: 'Determina si un número es par o impar.', starterCode: 'function esPar(numero) {\n  // TODO: retornar true si es par, false si es impar\n}', tests: ['esPar(4) === true', 'esPar(3) === false'] }
              ],
              hard: [
                { title: 'Factorial', prompt: 'Calcula el factorial de un número.', starterCode: 'function factorial(n) {\n  // TODO: calcular factorial\n}', tests: ['factorial(5) === 120'] }
              ]
            },
            estructuras: {
              easy: [
                { title: 'Imprimir números pares', prompt: 'Usa un bucle para imprimir números pares del 1 al 20.', starterCode: 'for(let i = 1; i <= 20; i++) {\n  // TODO: imprimir solo números pares\n}', tests: ['Debe imprimir 2, 4, 6, 8, 10, 12, 14, 16, 18, 20'] },
                { title: 'Contar hasta 10', prompt: 'Usa un bucle while para contar del 1 al 10.', starterCode: 'let contador = 1;\n// TODO: usar while para contar hasta 10', tests: ['Debe imprimir números del 1 al 10'] }
              ],
              medium: [
                { title: 'Contar vocales', prompt: 'Cuenta las vocales en una cadena de texto.', starterCode: 'function contarVocales(texto) {\n  // TODO: contar vocales a, e, i, o, u\n}', tests: ['contarVocales("hola") === 2'] },
                { title: 'Array de cuadrados', prompt: 'Crea un array con los cuadrados de números del 1 al 5.', starterCode: 'let cuadrados = [];\n// TODO: llenar array con cuadrados', tests: ['Array debe contener [1, 4, 9, 16, 25]'] }
              ]
            },
            algoritmos: {
              easy: [
                { title: 'Buscar en array', prompt: 'Busca un elemento en un array y retorna su posición.', starterCode: 'function buscar(array, elemento) {\n  // TODO: buscar elemento y retornar índice\n}', tests: ['buscar([1,2,3], 2) === 1'] }
              ],
              medium: [
                { title: 'Ordenar array', prompt: 'Ordena un array de números de menor a mayor.', starterCode: 'function ordenar(numeros) {\n  // TODO: ordenar array\n}', tests: ['ordenar([3,1,2]) debe retornar [1,2,3]'] }
              ]
            }
          };
          
          const byModule = exercises[module] || exercises.fundamentos;
          const byDiff = byModule[difficulty] || byModule.easy;
          const exerciseList = Array.isArray(byDiff) ? byDiff : [byDiff];
          const randomExercise = exerciseList[Math.floor(Math.random() * exerciseList.length)];
          
          return Promise.resolve({
            title: randomExercise.title,
            description: randomExercise.prompt,
            starterCode: randomExercise.starterCode,
            tests: randomExercise.tests,
            meta: { module, difficulty, source: 'mock' }
          });
        },
        getHint(context){
          const { module='fundamentos', currentChallenge } = context||{};
          const hints = {
            fundamentos: [
              'Recuerda usar console.log() para mostrar resultados',
              'Las variables se declaran con let o const',
              'Usa template strings con backticks para concatenar: `Hola ${nombre}`',
              'Los operadores básicos son +, -, *, /, %'
            ],
            estructuras: [
              'Los bucles for son útiles cuando sabes cuántas veces iterar',
              'Usa el operador % para verificar si un número es par: num % 2 === 0',
              'Los arrays se crean con [] y se acceden con índices: array[0]',
              'Puedes usar push() para agregar elementos a un array'
            ],
            algoritmos: [
              'Divide el problema en pasos más pequeños',
              'Usa métodos de array como find(), filter(), sort()',
              'Considera la complejidad temporal de tu solución',
              'Prueba tu código con casos límite'
            ]
          };
          const moduleHints = hints[module] || hints.fundamentos;
          const randomHint = moduleHints[Math.floor(Math.random() * moduleHints.length)];
          return Promise.resolve(randomHint);
        },
        evaluateSolution(code, challenge){
          // Evaluación básica del código
          const feedback = {
            success: true,
            message: 'Código evaluado. Verifica que pase las pruebas mostradas.',
            suggestions: []
          };
          
          // Análisis básico del código
          if (!code || code.trim().length < 10) {
            feedback.success = false;
            feedback.message = 'El código parece muy corto. Asegúrate de implementar la solución completa.';
          } else if (code.includes('TODO')) {
            feedback.success = false;
            feedback.message = 'Aún tienes comentarios TODO. Completa la implementación.';
          } else if (!code.includes('console.log') && !code.includes('return')) {
            feedback.suggestions.push('Considera usar console.log() para mostrar resultados o return para retornar valores.');
          }
          
          return Promise.resolve(feedback);
        }
      };
      return mock;
    },

    // Create new exercise based on selected language/difficulty and module
    async createNewExercise(){
      const thinking = document.getElementById('thinking');
      const feedback = document.getElementById('smartFeedback');
      if (thinking) thinking.style.display = 'block';
      try {
        const langSel = document.getElementById('exerciseLanguage');
        const diffSel = document.getElementById('exerciseDifficulty');
        this.state.language = langSel ? langSel.value : 'javascript';
        this.state.difficulty = diffSel ? diffSel.value : 'easy';
    
        // Detect module from URL
        const params = new URLSearchParams(window.location.search);
        const module = params.get('module') || 'fundamentos';
    
        let result;
        if (this.edu.generateChallenge) {
          result = await this.edu.generateChallenge(this.state.language, this.state.difficulty, 1);
        } else {
          result = await this.edu.createExercise({ language: this.state.language, difficulty: this.state.difficulty, module });
        }
        this.renderExercise(result);
        if (feedback) feedback.classList.add('show');
      } catch(e){
        const feedbackContent = document.getElementById('smartFeedbackContent');
        if (feedbackContent) feedbackContent.textContent = 'No se pudo generar el ejercicio. Intenta de nuevo.';
      } finally {
        if (thinking) thinking.style.display = 'none';
      }
    },

    // Render exercise into the game UI
    renderExercise(ex){
      const titleEl = document.getElementById('challengeTitle');
      const descEl = document.getElementById('challengeDesc');
      const codeEl = document.getElementById('codeEditor');
      if (titleEl) titleEl.textContent = ex.title || 'Ejercicio';
      if (descEl) descEl.textContent = ex.description || '';
      if (codeEl) codeEl.value = (ex.starterCode || ex.initialCode || '');
      this.state.currentChallenge = {
        title: ex.title || 'Ejercicio',
        description: ex.description || '',
        initialCode: (ex.starterCode || ex.initialCode || ''),
        expectedConcepts: ex.expectedConcepts || []
      };
      const codeSection = document.getElementById('codeSection');
      if (codeSection) codeSection.style.display = 'block';
      const feedback = document.getElementById('smartFeedback');
      const feedbackContent = document.getElementById('smartFeedbackContent');
      if (feedback) feedback.classList.add('show');
      if (feedbackContent) {
        const mod = (ex.meta && ex.meta.module) ? ex.meta.module : '-';
        const dif = (ex.meta && ex.meta.difficulty) ? ex.meta.difficulty : '-';
        feedbackContent.textContent = 'Módulo: ' + mod + ' · Dificultad: ' + dif;
      }
    },

    async runCode(){
      const editor = document.getElementById('codeEditor');
      const resultsPanel = document.getElementById('resultsPanel');
      const resultsContent = document.getElementById('resultsContent');
      const resultsTitle = document.getElementById('resultsTitle');
      const code = editor ? editor.value : '';
      let logs = [];
      let ok = true;
      let errorMsg = '';
      let lastErrorDetail = null;
      const originalLog = console.log;
      try {
        console.log = (...args)=> logs.push(args.join(' '));
        new Function(code)();
      } catch(err){
        ok = false;
        errorMsg = err && err.message ? err.message : String(err);
        lastErrorDetail = err && err.stack ? err.stack : errorMsg;
      } finally {
        console.log = originalLog;
      }
      // Persistimos contexto para enriquecer pistas
      this.state.lastLogs = logs;
      this.state.lastError = lastErrorDetail;
      if (resultsPanel) {
        resultsPanel.classList.remove('success','error');
        resultsPanel.classList.add(ok ? 'success' : 'error');
        resultsPanel.style.display = 'block';
      }
      if (resultsTitle) resultsTitle.textContent = ok ? 'Salida del programa' : 'Error de ejecución';
      if (resultsContent) {
        const out = ok ? (logs.join('\n') || '(sin salida)') : errorMsg;
        resultsContent.innerHTML = '<pre>' + out + '</pre>';
      }
    },

    async getSmartHint(){
      const thinking = document.getElementById('thinking');
      const hintPanel = document.getElementById('hintPanel');
      const hintText = document.getElementById('hintText');
      if (thinking) thinking.style.display = 'block';
      try {
        const editor = document.getElementById('codeEditor');
        const code = editor ? editor.value : '';
        const challenge = this.state.currentChallenge || { title: 'Ejercicio', description: '' };
        let hint;
        if (this.edu && typeof this.edu.generateHint === 'function') {
          hint = await this.edu.generateHint(code, challenge, this.state.lastError || '');
        } else if (this.edu && typeof this.edu.getHint === 'function') {
          const params = new URLSearchParams(window.location.search);
          const module = params.get('module') || 'fundamentos';
          hint = await this.edu.getHint({ module });
        } else {
          hint = 'Divide el problema y prueba casos simples.';
        }
        if (hintPanel) hintPanel.style.display = 'block';
        if (hintText) hintText.textContent = hint;
      } catch(e){
        if (hintPanel) hintPanel.style.display = 'block';
        if (hintText) hintText.textContent = 'No se pudo generar una pista ahora.';
      } finally {
        if (thinking) thinking.style.display = 'none';
      }
    },

    async submitSolution(){
      const editor = document.getElementById('codeEditor');
      const resultsPanel = document.getElementById('resultsPanel');
      const resultsContent = document.getElementById('resultsContent');
      const resultsTitle = document.getElementById('resultsTitle');
      const code = editor ? editor.value : '';
      let res;
      try {
        if (this.edu.evaluateCode) {
          const ch = this.state.currentChallenge || { description: '', expectedConcepts: [] };
          res = await this.edu.evaluateCode(code, ch.expectedConcepts || [], ch.description || '');
          const ok = res && res.isCorrect;
          if (resultsPanel) {
            resultsPanel.classList.remove('success','error');
            resultsPanel.classList.add(ok ? 'success' : 'error');
            resultsPanel.style.display = 'block';
          }
          if (resultsTitle) resultsTitle.textContent = 'Evaluación';
          if (resultsContent) {
            let html = '';
            html += '<div><strong>Puntuación:</strong> ' + (res.score || '-') + '</div>';
            html += '<div><strong>Feedback:</strong> ' + (res.feedback || '-') + '</div>';
            if (res.improvements && res.improvements.length) {
              html += '<div><strong>Mejoras:</strong> <ul>' + res.improvements.map(i=>'<li>'+i+'</li>').join('') + '</ul></div>';
            }
            html += '<div><strong>Siguientes pasos:</strong> ' + (res.nextSteps || '-') + '</div>';
            resultsContent.innerHTML = html;
          }
        } else {
          res = await this.edu.evaluateSolution(code);
          if (resultsPanel) {
            resultsPanel.classList.remove('success','error');
            resultsPanel.classList.add(res && res.success ? 'success' : 'error');
            resultsPanel.style.display = 'block';
          }
          if (resultsTitle) resultsTitle.textContent = 'Evaluación';
          if (resultsContent) {
            resultsContent.innerHTML = '<div>' + (res.message || (res.success ? '¡Bien hecho!' : 'Sigue intentando')) + '</div>';
          }
        }
      } catch(e){
        if (resultsPanel) {
          resultsPanel.classList.remove('success');
          resultsPanel.classList.add('error');
          resultsPanel.style.display = 'block';
        }
        if (resultsTitle) resultsTitle.textContent = 'Error en evaluación';
        if (resultsContent) resultsContent.innerHTML = '<div>Intenta nuevamente más tarde.</div>';
      }
    },

    // Inject a panel of key concepts into game page
    injectConceptsPanel(){
      const container = document.getElementById('codeSection');
      if (!container) return;
      const params = new URLSearchParams(window.location.search);
      const module = params.get('module') || 'fundamentos';
      const conceptsByModule = {
        fundamentos: {
          title: 'Conceptos clave: Fundamentos',
          bullets: [
            'Variable: espacio para guardar un valor (ej: let x = 5)',
            'Tipo de dato: número, cadena, booleano',
            'Operadores: + - * / % y comparaciones == === > <'
          ],
          snippet: 'let nombre = "Ana"; let edad = 15; console.log(`Hola ${nombre}, tienes ${edad} años`);'
        },
        estructuras: {
          title: 'Conceptos clave: Estructuras de control',
          bullets: [
            'Condicional if/else para decisiones',
            'Bucle for para recorridos con contador',
            'Operador % para detectar pares/impares'
          ],
          snippet: 'for (let i=1; i<=10; i++){ if (i % 2 === 0) console.log(i); }'
        }
      };
      const data = conceptsByModule[module] || conceptsByModule.fundamentos;
      const panel = document.createElement('div');
      panel.className = 'concepts-panel';
      panel.style.border = '1px solid var(--border-color)';
      panel.style.borderRadius = 'var(--radius)';
      panel.style.padding = '12px';
      panel.style.background = 'var(--bg-secondary)';
      panel.style.marginTop = '12px';
      panel.innerHTML = `
        <h4>${data.title}</h4>
        <ul style="margin-left: 16px;">
          ${data.bullets.map(b=>`<li>${b}</li>`).join('')}
        </ul>
        <div style="background: rgba(16,185,129,0.08); padding: 8px; border-radius: 8px;">
          <strong>Ejemplo rápido (JS):</strong>
          <pre style="white-space: pre-wrap; margin-top: 6px;">${data.snippet}</pre>
        </div>
      `;
      container.appendChild(panel);
    }
  };

  // When DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', ()=> gameEngine.init());
  } else {
    try { gameEngine.init(); } catch(_) {}
  }
})();