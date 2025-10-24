(function(){
  'use strict';

  var navItems = document.querySelectorAll('.student-nav .nav-item');
  var sectionMap = {
    home: ['#home-section'],
    progress: ['#progress-section', '#progress-tracking-section'],
    exercises: ['#exercises-section'],
    messages: ['#messages-section'],
    'basic-modules': ['#progress-section']
  };

  function showSections(selectors){
    document.querySelectorAll('.section').forEach(function(s){ s.style.display = 'none'; });
    (selectors || []).forEach(function(sel){ var el = document.querySelector(sel); if(el){ el.style.display = 'block'; } });
  }

  function activateNav(sectionKey){
    navItems.forEach(function(n){ n.classList.remove('active'); });
    var target = Array.prototype.find.call(navItems, function(n){ return n.getAttribute('data-section') === sectionKey; });
    if (target) { target.classList.add('active'); }
  }

  navItems.forEach(function(a){
    a.addEventListener('click', function(e){
      var section = this.getAttribute('data-section');
      if(!section) return;

      if (section === 'exercises') {
        // Redirige a Mis Ejercicios (modo juego)
        return window.location.assign('/student/game');
      }

      e.preventDefault();
      activateNav(section);
      var targets = sectionMap[section] || sectionMap.home;
      showSections(targets);
      var nextHash = '#' + section;
      try { history.replaceState(null, '', nextHash); } catch(_) {}

      if (section === 'basic-modules') {
        var mod = document.getElementById('basic-modules');
        if (mod) {
          try { mod.scrollIntoView({ behavior: 'smooth', block: 'start' }); } catch(_) {}
        }
      }
    });
  });

  // Chat con Asistente IA
  var sendBtn = document.getElementById('sendChat');
  var input = document.getElementById('chatInput');
  var messages = document.getElementById('chatMessages');

  function appendMessage(role, content){
    var item = document.createElement('div');
    item.className = 'chat-item ' + role;
    item.style.padding = '10px';
    item.style.borderRadius = '8px';
    item.style.background = role === 'user' ? 'var(--bg-primary)' : 'rgba(16, 185, 129, 0.08)';
    item.style.border = '1px solid var(--border-color)';
    item.innerHTML = '<strong>' + (role === 'user' ? 'Tú' : 'Asistente') + ':</strong> ' + content;
    messages.appendChild(item);
    messages.scrollTop = messages.scrollHeight;
  }

  var ses;
  try { ses = new (window.SmartEducationSystem || function(){})(); } catch(e) { ses = null; }

  async function sendMessage(){
    var text = (input && input.value || '').trim();
    if(!text) return;
    appendMessage('user', text);
    input.value = '';
    appendMessage('assistant', 'Pensando...');
    var thinkingNode = messages.lastChild;

    try {
      if (!ses || typeof ses.makeRequest !== 'function') throw new Error('Sistema IA no disponible');
      var reply = await ses.makeRequest([
        { role: 'system', content: 'Eres un tutor de programación para secundaria/bachillerato. Ayuda con explicaciones técnicas claras y motivadoras.' },
        { role: 'user', content: text }
      ]);
      thinkingNode.innerHTML = '<strong>Asistente:</strong> ' + (reply || 'Sin respuesta');
    } catch (e) {
      thinkingNode.innerHTML = '<strong>Asistente:</strong> ' + 'Estoy en modo offline. Sugerencia: revisa la sintaxis y estructura lógica, define variables y prueba en pequeños pasos.';
    }
  }

  if (sendBtn) {
    sendBtn.addEventListener('click', sendMessage);
  }
  if (input) {
    input.addEventListener('keydown', function(e){
      if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
    });
  }

  // Inicializar según el hash actual
  var initialSection = (window.location.hash || '').replace('#','');
  if (initialSection === 'exercises') {
    window.location.assign('/student/game');
  } else {
    activateNav(initialSection || 'home');
    showSections(sectionMap[initialSection] || sectionMap.home);
    if (initialSection === 'basic-modules') {
      var modInit = document.getElementById('basic-modules');
      if (modInit) {
        try { modInit.scrollIntoView({ behavior: 'smooth', block: 'start' }); } catch(_) {}
      }
    }
  }
})();

// Controlador de navegación del panel de estudiante
(function () {
  const nav = document.querySelector('.student-nav');
  if (!nav) return;

  const sectionsMap = {
    home: '#home-section',
    progress: '#progress-section',
    'basic-modules': '#basic-modules-section',
    exercises: '#exercises-section',
    messages: '#messages-section'
  };

  function showSection(id) {
    document.querySelectorAll('.section').forEach(s => {
      s.style.display = 'none';
    });
    const el = document.querySelector(id);
    if (el) {
      el.style.display = '';
      // Si la sección seleccionada contiene el ancla interno, hacer scroll hasta él
      if (id === '#basic-modules-section') {
        const anchor = document.querySelector('#basic-modules');
        if (anchor) anchor.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }
  }

  // Estado inicial
  showSection('#home-section');

  nav.addEventListener('click', function (e) {
    const link = e.target.closest('a[data-section]');
    if (!link) return;
    e.preventDefault();
    const sectionKey = link.getAttribute('data-section');
    const targetId = sectionsMap[sectionKey];
    if (targetId) {
      showSection(targetId);
      // Actualizar estado activo
      nav.querySelectorAll('.nav-item').forEach(a => a.classList.remove('active'));
      link.classList.add('active');
    }
  });
})();