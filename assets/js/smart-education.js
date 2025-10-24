/* SmartEducationSystem - Integración de generación de ejercicios en Algorix */
(function(){
  'use strict';

  // Utilidad sencilla de sanitización
  function safeText(v){ return (v == null ? '' : String(v)); }

  class SmartEducationSystem {
    constructor(cfg = {}){
      // Mezclar configuración del constructor con window.AI_CONFIG
      const wcfg = (typeof window !== 'undefined' && window.AI_CONFIG) || {};
      const merged = Object.assign({}, wcfg, cfg);
      this.endpoint = merged.endpoint || 'https://oi-server.onrender.com/chat/completions';
      this.model = merged.model || 'openrouter/claude-sonnet-4';
      this.headers = { 'Content-Type': 'application/json' };
      if (merged.customerId) { this.headers['customerId'] = merged.customerId; }
      if (merged.token) { this.headers['Authorization'] = 'Bearer ' + merged.token; }
      // online si hay endpoint y no offline, y además hay token o se fuerza online
      this.online = !!(this.endpoint && !merged.offline && (merged.token || merged.forceOnline));
      this.initializePrompts();
    }

    initializePrompts(){
      this.challengePrompt = `Eres un profesor muy amable que enseña programación a estudiantes de secundaria y bachillerato (14-18 años).

IMPORTANTE: Usa un lenguaje APROPIADO para la edad, pero NO infantil. Los estudiantes de secundaria y bachillerato pueden manejar conceptos más complejos.

INSTRUCCIONES:
1. Crea ejercicios apropiados para estudiantes de secundaria/bachillerato
2. Usa explicaciones claras pero técnicamente correctas
3. Incluye conceptos progresivos de programación
4. Haz que sea educativo y motivador
5. Usa conceptos que se enseñan en estos niveles educativos

FORMATO (JSON):
{
  "title": "Título del ejercicio",
  "description": "Explicación técnica clara",
  "language": "Lenguaje",
  "difficulty": "Dificultad",
  "points": "100-300",
  "initialCode": "Código inicial con comentarios técnicos",
  "expectedConcepts": ["concepto1", "concepto2"],
  "hints": ["Pista técnica 1", "Pista técnica 2"],
  "solution": "Solución con comentarios explicativos"
}`;

      this.evaluationPrompt = `Eres un profesor evaluando a estudiantes de secundaria/bachillerato en programación.

EVALÚA con RIGOR ACADÉMICO apropiado:
1. ¿El código funciona correctamente?
2. ¿Aplica los conceptos correctamente?
3. ¿Qué puede mejorar técnicamente?
4. ¿Qué conceptos ha aprendido?

RESPUESTA (JSON):
{
  "isCorrect": boolean,
  "score": "0-100",
  "feedback": "Retroalimentación técnica motivadora",
  "improvements": ["mejora técnica específica"],
  "nextSteps": "Siguientes conceptos a aprender"
}

SÉ CONSTRUCTIVO y usa términos técnicos apropiados.`;

      this.hintPrompt = `Eres un tutor ayudando a un estudiante de secundaria/bachillerato con programación.

INSTRUCCIONES:
1. Usa vocabulario técnico apropiado para la edad
2. Da pistas progresivas, no la solución completa
3. Sé motivador pero técnicamente preciso
4. Explica conceptos de programación

RESPONDE SOLO CON LA PISTA (máximo 150 palabras).`;
    }

    isOffline(){
      var cfg = (typeof window !== 'undefined' && window.AI_CONFIG) || {};
      if (cfg.forceOnline) return false;
      return !!cfg.offline || !!document.querySelector('.offline-banner') || !this.online;
    }

    getEducationalTopics(language, difficulty){
      const topics = {
        javascript: {
          easy: ['variables', 'tipos básicos', 'console.log', 'condicionales simples'],
          medium: ['funciones', 'arrays', 'bucles for/while', 'objetos básicos'],
          hard: ['recursividad', 'closures', 'manejo de errores', 'APIs básicas']
        },
        python: {
          easy: ['variables', 'tipos básicos', 'print', 'condicionales'],
          medium: ['listas', 'funciones', 'bucles', 'diccionarios'],
          hard: ['recursividad', 'clases', 'excepciones', 'archivos']
        },
        'html-css': {
          easy: ['etiquetas básicas', 'texto', 'colores', 'estilos inline'],
          medium: ['layout', 'flexbox', 'selectores CSS', 'responsivo básico'],
          hard: ['grid', 'animaciones CSS', 'semántica avanzada', 'accesibilidad']
        },
        java: {
          easy: ['clases básicas', 'main', 'System.out.println', 'condicionales'],
          medium: ['métodos', 'arrays', 'bucles', 'POO básica'],
          hard: ['herencia', 'interfaces', 'excepciones', 'colecciones']
        }
      };
      const lang = topics[language] || topics.javascript;
      const dif = lang[difficulty] || lang.easy;
      return dif.join(', ');
    }

    async generateChallenge(language, difficulty, studentLevel = 1){
      // Fallback a mock si está offline o si la solicitud falla
      if (this.isOffline()) {
        return this.mockChallenge(language, difficulty, studentLevel);
      }
      const prompt = `${this.challengePrompt}

CREAR PARA:
- Lenguaje: ${language}
- Dificultad: ${difficulty}
- Nivel del estudiante: ${studentLevel}

TEMAS para secundaria/bachillerato en ${language}:
${this.getEducationalTopics(language, difficulty)}

Crea UN ejercicio educativo apropiado para estudiantes de secundaria/bachillerato.`;
      try {
        const responseText = await this.makeRequest([
          { role: 'system', content: 'Eres un profesor de programación para secundaria y bachillerato.' },
          { role: 'user', content: prompt }
        ]);
        return this.parseResponse(responseText);
      } catch (e) {
        return this.mockChallenge(language, difficulty, studentLevel);
      }
    }

    mockChallenge(language, difficulty, studentLevel){
      return {
        title: `Bienvenida en ${language}`,
        description: `Escribe un programa que imprima los números del 1 al 10 y, luego, muestre la suma total. Enfócate en sintaxis correcta y bucles.`,
        language,
        difficulty,
        points: 120,
        initialCode: language === 'javascript'
          ? `// Imprime 1..10 y calcula la suma\nlet suma = 0;\nfor (let i = 1; i <= 10; i++) {\n  console.log(i);\n  suma += i;\n}\nconsole.log('Suma total:', suma);`
          : `# Imprime 1..10 y calcula la suma\nsuma = 0\nfor i in range(1, 11):\n    print(i)\n    suma += i\nprint('Suma total:', suma)`,
        expectedConcepts: ['bucles', 'acumuladores', 'salida estándar'],
        hints: ['Comprueba los límites del bucle', 'Usa una variable para acumular'],
        solution: 'Implementa el bucle y suma incrementando en cada iteración.'
      };
    }

    async evaluateCode(code, expectedConcepts, challengeDescription){
      const prompt = `${this.evaluationPrompt}

EJERCICIO: ${challengeDescription}
CONCEPTOS ESPERADOS: ${expectedConcepts.join(', ')}

CÓDIGO DEL ESTUDIANTE:
\`\`\`
${code}
\`\`\`

Evalúa con rigor académico apropiado para secundaria/bachillerato.`;
      try {
        const responseText = await this.makeRequest([
          { role: 'system', content: 'Eres un profesor de programación evaluando estudiantes de secundaria/bachillerato.' },
          { role: 'user', content: prompt }
        ]);
        return JSON.parse(responseText);
      } catch (error) {
        return {
          isCorrect: false,
          score: 40,
          feedback: 'Buen intento. Revisa los conceptos y la sintaxis. Continúa practicando.',
          improvements: ['Revisa la sintaxis del código'],
          nextSteps: 'Repasa los conceptos básicos y sigue practicando.'
        };
      }
    }

    async generateHint(code, challenge, errorContext = ''){
      const prompt = `${this.hintPrompt}

EJERCICIO: ${challenge.title}
DESCRIPCIÓN: ${challenge.description}

CÓDIGO ACTUAL:
\`\`\`
${code}
\`\`\`

ERROR: ${errorContext}

Da una pista técnica apropiada para un estudiante de secundaria/bachillerato.`;
      try {
        const responseText = await this.makeRequest([
          { role: 'system', content: 'Eres un tutor de programación para estudiantes de secundaria/bachillerato.' },
          { role: 'user', content: prompt }
        ]);
        return responseText.trim();
      } catch (error) {
        const ec = (errorContext || '').toString().slice(0, 200);
        return ec
          ? `Error detectado: ${ec}. Sugerencia: revisa la línea indicada, la sintaxis (paréntesis, llaves y comillas) y valida el flujo lógico. Prueba el código por partes.`
          : 'Revisa la sintaxis y la lógica de tu código. Asegúrate de que cada línea tenga el propósito correcto y esté bien estructurada.';
      }
    }

    async makeRequest(messages, timeout = 25000){
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), timeout);
      try {
        const res = await fetch(this.endpoint, {
          method: 'POST',
          headers: this.headers,
          body: JSON.stringify({ model: this.model, messages }),
          signal: controller.signal
        });
        clearTimeout(timeoutId);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        const content = (data && (
          (data.choices && data.choices[0] && data.choices[0].message && data.choices[0].message.content) ||
          data.content ||
          data.output ||
          (data.message && data.message.content)
        )) || '';
        return typeof content === 'string' ? content : JSON.stringify(content);
      } catch (e) {
        clearTimeout(timeoutId);
        throw e;
      }
    }

    parseResponse(response){
      if (typeof response !== 'string') {
        return response;
      }
      try { return JSON.parse(response); } catch(_){}
      const match = response.match(/\{[\s\S]*\}/);
      if (match) {
        try { return JSON.parse(match[0]); } catch(_){}
      }
      return {
        title: 'Ejercicio generado',
        description: safeText(response).slice(0, 400),
        language: '',
        difficulty: '',
        points: 100,
        initialCode: '',
        expectedConcepts: [],
        hints: [],
        solution: ''
      };
    }
  }

  // Exponer en window (si no existe ya)
  if (typeof window !== 'undefined') {
    window.SmartEducationSystem = SmartEducationSystem;
  }

})();