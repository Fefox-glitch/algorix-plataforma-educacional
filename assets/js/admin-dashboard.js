(function(){
  'use strict';

  // Navegación de la barra lateral del admin
  var navItems = document.querySelectorAll('.admin-nav .nav-item');

  // Toggle de secciones colapsables en el sidebar
  (function initSidebarToggles(){
    var toggles = document.querySelectorAll('.sidebar-section-toggle');
    if (!toggles || !toggles.length) return;
    toggles.forEach(function(btn){
      btn.addEventListener('click', function(){
        var targetId = btn.getAttribute('data-target');
        var grp = document.getElementById(targetId);
        if (!grp) return;
        var isCollapsed = btn.classList.toggle('collapsed');
        grp.style.display = isCollapsed ? 'none' : 'block';
        var caret = btn.querySelector('.caret');
        if (caret) caret.classList.toggle('rotated', !isCollapsed);
      });
    });
  })();

  function hideAllSections(){
    document.querySelectorAll('.admin-content section').forEach(function(s){ s.style.display = 'none'; s.classList.remove('active'); });
  }

  function showSection(id){
    hideAllSections();
    var el = document.getElementById(id);
    if (el) { el.style.display = 'block'; el.classList.add('active'); }
    // actualizar estado activo en la barra lateral
    navItems.forEach(function(n){ n.classList.remove('active'); });
    var target = Array.prototype.find.call(navItems, function(n){ return n.getAttribute('data-section') === id; });
    if (target) { target.classList.add('active'); }
    try { history.replaceState(null, '', '#' + id); } catch(_) {}
  }

  // Añadir manejadores de clic para cada opción del menú lateral (compatibilidad)
  navItems.forEach(function(a){
    a.addEventListener('click', function(e){
      e.preventDefault();
      var id = a.getAttribute('data-section');
      if (id) { window.showSection ? window.showSection(id) : showSection(id); }
    });
  });

  // Delegación de eventos en la barra lateral (robusto ante contenido dinámico)
  var adminNav = document.querySelector('.admin-nav');
  if (adminNav) {
    adminNav.addEventListener('click', function(e){
      var link = e.target.closest('.nav-item[data-section]');
      if (!link) return;
      e.preventDefault();
      var id = link.getAttribute('data-section');
      if (id) { window.showSection ? window.showSection(id) : showSection(id); }
    });
  }

  // Map de paneles avanzados -> sección de destino
  var panelMap = {
    'usuarios': 'panel-usuarios',
    'analytics': 'panel-analytics',
    'notas': 'panel-notas',
    'exportacion': 'panel-exportacion',
    'configuracion': 'panel-ai-config',
    'codigos': 'panel-codigos',
    'comunicacion': 'panel-comunicacion',
    'analytics-ia': 'panel-analytics-ia',
    'codigo-inicio': 'panel-codigos'
  };

  window.showAdminPanel = function(key){
    var id = panelMap[key];
    if (!id) return;
    hideAllSections();
    var el = document.getElementById(id);
    if (el) el.style.display = 'block';
    try { history.replaceState(null, '', '#' + key); } catch(_) {}
  };

  window.goAdminSection = function(id){ window.showSection ? window.showSection(id) : showSection(id); };

  // Estado inicial
  (function init(){
    var hash = (window.location.hash || '').replace('#', '');
    if (hash && panelMap[hash]) {
      window.showAdminPanel(hash);
    } else {
      var candidate = hash || 'overview';
      var exists = document.getElementById(candidate);
      showSection(exists ? candidate : 'overview');
    }
  })();
})();

  // Utilidades de API (respetan BASE_URL cuando la app corre en subruta)
  function withBase(url){
    var base = (window.BASE_URL || '').replace(/\/$/, '');
    return (typeof url === 'string' && url.indexOf('/') === 0) ? (base + url) : url;
  }
  async function apiGet(url){
    try {
      const r = await fetch(withBase(url));
      const j = await r.json();
      return { ok: r.ok, status: r.status, data: j.data || [], error: j.error || null };
    } catch(e){ return { ok:false, status:0, data:[], error:e?.message||'network' }; }
  }
  async function apiPost(url, body){
    try {
      const headers = { 'Content-Type': 'application/json' };
      if (window.CSRF_TOKEN) headers['X-CSRF-Token'] = window.CSRF_TOKEN;
      const r = await fetch(withBase(url), { method:'POST', headers, body: JSON.stringify(body||{}) });
      const j = await r.json();
      return { ok: r.ok, status: r.status, data: j.data || [], error: j.error || null };
    } catch(e){ return { ok:false, status:0, data:[], error:e?.message||'network' }; }
  }
  async function apiPatch(url, body){
    try {
      const headers = { 'Content-Type': 'application/json' };
      if (window.CSRF_TOKEN) headers['X-CSRF-Token'] = window.CSRF_TOKEN;
      const r = await fetch(withBase(url), { method:'PATCH', headers, body: JSON.stringify(body||{}) });
      const j = await r.json();
      return { ok: r.ok, status: r.status, data: j.data || [], error: j.error || null };
    } catch(e){ return { ok:false, status:0, data:[], error:e?.message||'network' }; }
  }

  // Configuración IA: cargar y guardar
  async function loadAIConfig(){
    const statusEl = document.getElementById('aiConfigStatus');
    const preview = document.getElementById('aiConfigPreview');
    const ep = document.getElementById('ai_endpoint');
    const model = document.getElementById('ai_model');
    const token = document.getElementById('ai_token');
    const customer = document.getElementById('ai_customer');
    const offline = document.getElementById('ai_offline');
    if (statusEl) statusEl.textContent = 'Cargando configuración...';
    const res = await apiGet('/api/admin/ai-config');
    if (!res.ok){ if(statusEl) statusEl.textContent = 'Error '+res.status+': '+(res.error||''); return; }
    const d = res.data || {};
    if (ep) ep.value = d.AI_ENDPOINT || '';
    if (model) model.value = d.AI_MODEL || '';
    if (token) token.value = d.AI_TOKEN || '';
    if (customer) customer.value = d.AI_CUSTOMER_ID || '';
    if (offline) offline.checked = (d.OFFLINE_MODE === true || d.OFFLINE_MODE === 'true');
    if (preview) preview.textContent = JSON.stringify(d, null, 2);
    if (statusEl) statusEl.textContent = 'Configuración cargada.';
  }
  window.saveAIConfig = async function(){
    const statusEl = document.getElementById('aiConfigStatus');
    const preview = document.getElementById('aiConfigPreview');
    const payload = {
      AI_ENDPOINT: (document.getElementById('ai_endpoint')?.value||'').trim(),
      AI_MODEL: (document.getElementById('ai_model')?.value||'').trim(),
      AI_TOKEN: (document.getElementById('ai_token')?.value||'').trim(),
      AI_CUSTOMER_ID: (document.getElementById('ai_customer')?.value||'').trim(),
      OFFLINE_MODE: !!(document.getElementById('ai_offline')?.checked)
    };
    if (statusEl) statusEl.textContent = 'Guardando...';
    const res = await apiPost('/api/admin/ai-config', payload);
    if (!res.ok){ if(statusEl) statusEl.textContent = 'Error '+res.status+': '+(res.error||''); return; }
    if (preview) preview.textContent = JSON.stringify(res.data || {}, null, 2);
    if (statusEl) statusEl.textContent = 'Guardado correctamente.';
    alert('Configuración de IA guardada');
  };

  // Render: Laboratorios
  async function loadLabs(){
    const res = await apiGet('/api/admin/labs');
    const cont = document.getElementById('labs-container');
    const select = document.getElementById('lab-action-select');
    if (!cont) return;
    cont.innerHTML = '';
    if (!res.ok){
      cont.innerHTML = '<p class="error">Error '+res.status+': '+(res.error||'fallo de API')+'</p>';
      if (select) select.innerHTML = '<option value="">Seleccione un laboratorio</option>';
      return;
    }
    if (!Array.isArray(res.data) || res.data.length===0){
      cont.innerHTML = '<p>No hay laboratorios disponibles.</p>';
      if (select) select.innerHTML = '<option value="">Seleccione un laboratorio</option>';
      return;
    }
    window.__labsCache = res.data;
    const frag = document.createDocumentFragment();
    res.data.forEach(function(l){
      const card = document.createElement('div');
      card.className = 'card';
      card.setAttribute('data-id', l.id);
      card.innerHTML = '<h3>'+(l.name||('Lab '+l.id))+'</h3>'+
        '<p>Ubicación: '+(l.location||'-')+'</p>'+
        '<p>Capacidad: '+(l.capacity||0)+'</p>'+
        '<p>Estado: '+((l.is_active===false)?'inactivo':'activo')+'</p>'+
        '<div class="card-actions">'+
          '<button class="btn btn-primary" onclick="editLab('+l.id+')">Editar</button>'+
          '<button class="btn btn-danger" onclick="deleteLab('+l.id+')">Eliminar</button>'+
        '</div>';
      frag.appendChild(card);
    });
    cont.appendChild(frag);
    // Resaltar recién creado
    if (window.__lastCreatedLabId){
      const newCard = cont.querySelector('.card[data-id="'+window.__lastCreatedLabId+'"]');
      if (newCard){
        newCard.style.outline = '3px solid #28a745';
        setTimeout(function(){ newCard.style.outline=''; }, 2000);
      }
      window.__lastCreatedLabId = null;
    }
    // Popular select para acciones
    if (select){
      select.innerHTML = '<option value="">Seleccione un laboratorio</option>';
      res.data.forEach(function(l){
        const opt = document.createElement('option');
        opt.value = l.id;
        opt.textContent = (l.name||('Lab '+l.id));
        select.appendChild(opt);
      });
    }
    const labsCounter = document.getElementById('total-labs');
    if (labsCounter) labsCounter.textContent = res.data.length;
  }

  // Render: Computadoras
  async function loadComputers(status){
    const qs = status && status!=='all' ? ('?status='+encodeURIComponent(status)) : '';
    const res = await apiGet('/api/admin/computers'+qs);
    const tbody = document.getElementById('computers-tbody');
    if (!tbody) return;
    tbody.innerHTML = '';
    if (!res.ok){
      const tr = document.createElement('tr');
      tr.innerHTML = '<td colspan="7" class="error">Error '+res.status+': '+(res.error||'fallo de API')+'</td>';
      tbody.appendChild(tr);
      const totalCounter = document.getElementById('total-computers');
      const onlineCounter = document.getElementById('online-computers');
      if (totalCounter) totalCounter.textContent = '0';
      if (onlineCounter) onlineCounter.textContent = '0';
      return;
    }
    if (!Array.isArray(res.data) || res.data.length===0){
      const tr = document.createElement('tr');
      tr.innerHTML = '<td colspan="7">No hay computadoras.</td>';
      tbody.appendChild(tr);
      const totalCounter = document.getElementById('total-computers');
      const onlineCounter = document.getElementById('online-computers');
      if (totalCounter) totalCounter.textContent = '0';
      if (onlineCounter) onlineCounter.textContent = '0';
      return;
    }
    let onlineCount = 0;
    window.__computersCache = res.data;
    res.data.forEach(function(c){
      const s = (c.status||'offline').toLowerCase();
      if (s==='online') onlineCount++;
      const tr = document.createElement('tr');
      tr.setAttribute('data-id', c.id);
      tr.innerHTML = '<td>'+(c.name||('PC-'+c.id))+'</td>'+
        '<td>'+(c.lab_id||'-')+'</td>'+
        '<td>'+(c.ip_address||'-')+'</td>'+
        '<td>'+(c.mac_address||'-')+'</td>'+
        '<td>'+(s)+'</td>'+
        '<td>'+(c.last_seen||'-')+'</td>'+
        '<td>\n          <button class="btn btn-primary" onclick="editComputer('+c.id+')">Editar</button>\n          <button class="btn btn-danger" onclick="deleteComputer('+c.id+')">Eliminar</button>\n        </td>';
      tbody.appendChild(tr);
    });
    const totalCounter = document.getElementById('total-computers');
    const onlineCounter = document.getElementById('online-computers');
    if (totalCounter) totalCounter.textContent = res.data.length;
    if (onlineCounter) onlineCounter.textContent = onlineCount;
    // Resaltar recién creada
    if (window.__lastCreatedComputerId){
      const newRow = tbody.querySelector('tr[data-id="'+window.__lastCreatedComputerId+'"]');
      if (newRow){
        newRow.style.outline = '3px solid #28a745';
        setTimeout(function(){ newRow.style.outline=''; }, 2000);
      }
      window.__lastCreatedComputerId = null;
    }
  }
  window.filterComputers = function(){
    const sel = document.getElementById('computer-filter');
    const v = sel ? sel.value : 'all';
    loadComputers(v);
  };

  // Render: Grupos
  async function loadGroups(){
    const res = await apiGet('/api/admin/groups');
    const cont = document.getElementById('groups-container');
    if (!cont) return;
    cont.innerHTML = '';
    if (!res.ok){
      cont.innerHTML = '<p class="error">Error '+res.status+': '+(res.error||'fallo de API')+'</p>';
      const groupsCounter = document.getElementById('total-groups');
      if (groupsCounter) groupsCounter.textContent = '0';
      return;
    }
    if (!Array.isArray(res.data) || res.data.length===0){
      cont.innerHTML = '<p>No hay grupos registrados.</p>';
      const groupsCounter = document.getElementById('total-groups');
      if (groupsCounter) groupsCounter.textContent = '0';
      return;
    }
    window.__groupsCache = res.data;
    const frag = document.createDocumentFragment();
    res.data.forEach(function(g){
      const card = document.createElement('div');
      card.className = 'card';
      card.setAttribute('data-id', g.id);
      card.innerHTML = '<h3>'+(g.name||('Grupo '+g.id))+'</h3>'+
        '<p>Lab: '+(g.lab_id||'-')+'</p>'+
        '<p>Activo: '+((g.is_active===false)?'no':'sí')+'</p>'+
        '<div class="card-actions">'+
          '<button class="btn btn-primary" onclick="editGroup('+g.id+')">Editar</button>'+
          '<button class="btn btn-danger" onclick="deleteGroup('+g.id+')">Eliminar</button>'+
        '</div>';
      frag.appendChild(card);
    });
    cont.appendChild(frag);
    // Resaltar recién creado
    if (window.__lastCreatedGroupId){
      const newCard = cont.querySelector('.card[data-id="'+window.__lastCreatedGroupId+'"]');
      if (newCard){
        newCard.style.outline = '3px solid #28a745';
        setTimeout(function(){ newCard.style.outline=''; }, 2000);
      }
      window.__lastCreatedGroupId = null;
    }
    const groupsCounter = document.getElementById('total-groups');
    if (groupsCounter) groupsCounter.textContent = res.data.length;
  }

  // Render: Profesores
  async function loadTeachers(){
    const res = await apiGet('/api/admin/teachers');
    const tbody = document.getElementById('teachers-tbody');
    if (!tbody) return;
    tbody.innerHTML = '';
    if (!res.ok){
      const tr = document.createElement('tr');
      tr.innerHTML = '<td colspan="5" class="error">Error '+res.status+': '+(res.error||'fallo de API')+'</td>';
      tbody.appendChild(tr);
      return;
    }
    if (!Array.isArray(res.data) || res.data.length===0){
      const tr = document.createElement('tr');
      tr.innerHTML = '<td colspan="5">No hay profesores.</td>';
      tbody.appendChild(tr);
      return;
    }
    res.data.forEach(t => {
      const tr = document.createElement('tr');
      tr.innerHTML = '<td>'+(t.name||'-')+'</td>'+
        '<td>'+(t.email||'-')+'</td>'+
        '<td>-</td>'+
        '<td>'+(t.created_at||'-')+'</td>'+
        '<td><button class="btn btn-secondary" disabled>Ver</button></td>';
      tbody.appendChild(tr);
    });
  }

  // Render: Acciones
  async function loadActions(){
    const tbody = document.getElementById('actions-tbody');
    if (!tbody) return;
    const res = await apiGet('/api/admin/actions');
    tbody.innerHTML = '';
    if (!res.ok){
      const tr = document.createElement('tr');
      tr.innerHTML = '<td colspan="6" class="error">Error '+res.status+': '+(res.error||'fallo de API')+'</td>';
      tbody.appendChild(tr);
      return;
    }
    if (!Array.isArray(res.data) || res.data.length===0){
      const tr = document.createElement('tr');
      tr.innerHTML = '<td colspan="6">Sin acciones recientes.</td>';
      tbody.appendChild(tr);
      return;
    }
    res.data.forEach(a => {
      const tr = document.createElement('tr');
      tr.innerHTML = '<td>'+(a.performed_at||'-')+'</td>'+
        '<td>'+(a.computer_id||'-')+'</td>'+
        '<td>'+(a.action_type||'-')+'</td>'+
        '<td>'+(a.performed_by||'-')+'</td>'+
        '<td>'+(a.status||'-')+'</td>'+
        '<td>'+(a.result||'-')+'</td>';
      tbody.appendChild(tr);
    });
  }

  // Render: Sesiones
  async function loadSessions(){
    const tbody = document.getElementById('sessions-tbody');
    if (!tbody) return;
    const res = await apiGet('/api/admin/sessions');
    tbody.innerHTML = '';
    if (!res.ok){
      const tr = document.createElement('tr');
      tr.innerHTML = '<td colspan="7" class="error">Error '+res.status+': '+(res.error||'fallo de API')+'</td>';
      tbody.appendChild(tr);
      return;
    }
    if (!Array.isArray(res.data) || res.data.length===0){
      const tr = document.createElement('tr');
      tr.innerHTML = '<td colspan="7">No hay sesiones.</td>';
      tbody.appendChild(tr);
      return;
    }
    res.data.forEach(s => {
      const tr = document.createElement('tr');
      tr.innerHTML = '<td>'+(s.lab_id||'-')+'</td>'+
        '<td>'+(s.group_id||'-')+'</td>'+
        '<td>'+(s.teacher_id||'-')+'</td>'+
        '<td>'+(s.started_at||'-')+'</td>'+
        '<td>'+(s.ended_at||'-')+'</td>'+
        '<td>-</td>'+
        '<td>'+((s.ended_at)?'finalizada':'activa')+'</td>';
      tbody.appendChild(tr);
    });
  }

  // Acciones masivas por laboratorio
  window.performLabAction = async function(action){
    const sel = document.getElementById('lab-action-select');
    const labId = sel ? parseInt(sel.value,10) : 0;
    if (!labId){ alert('Seleccione un laboratorio'); return; }
    const res = await apiPost('/api/admin/actions/lab', { lab_id: labId, action_type: action });
    if (!res.ok){ alert('Error al programar acciones: '+(res.error||res.status)); return; }
    alert('Acciones programadas para '+(res.data?.count||0)+' computadoras');
    loadActions();
  };

  // Crear entidades rápidamente (prompts simples)
  // Helper para abrir modales
  function openModal(id){
    var m = document.getElementById(id);
    if (m){ m.style.display = 'block'; m.setAttribute('aria-hidden','false'); }
  }
  
  // Helpers para popular selects de Labs y Profesores
  async function populateLabSelect(selectId){
    const sel = document.getElementById(selectId);
    if (!sel) return;
    sel.innerHTML = '<option value="">Seleccione un laboratorio</option>';
    const res = await apiGet('/api/admin/labs');
    if (!res.ok) return;
    (res.data||[]).forEach(l => {
      const opt = document.createElement('option');
      opt.value = l.id;
      opt.textContent = (l.name || ('Lab ' + l.id));
      sel.appendChild(opt);
    });
  }
  
  async function populateTeacherSelect(selectId){
    const sel = document.getElementById(selectId);
    if (!sel) return;
    sel.innerHTML = '<option value="">Seleccione un profesor</option>';
    const res = await apiGet('/api/admin/teachers');
    if (!res.ok) return;
    (res.data||[]).forEach(t => {
      const opt = document.createElement('option');
      // id en users es UUID; fallback a uuid si existe
      opt.value = t.id || t.uuid;
      opt.textContent = t.name || t.full_name || t.email || (t.id ? String(t.id).substring(0,8) : 'Profesor');
      sel.appendChild(opt);
    });
  }

  // Reemplazo: crear laboratorio usando modal y formulario
  // Avisos simples
  function showToast(msg, type){
    const t = document.createElement('div');
    t.textContent = msg;
    t.style.position = 'fixed';
    t.style.right = '16px';
    t.style.bottom = '16px';
    t.style.padding = '10px 14px';
    t.style.background = (type==='error') ? '#c0392b' : '#2ecc71';
    t.style.color = '#fff';
    t.style.borderRadius = '6px';
    t.style.boxShadow = '0 2px 8px rgba(0,0,0,0.2)';
    t.style.zIndex = '9999';
    document.body.appendChild(t);
    setTimeout(function(){
      t.style.transition = 'opacity 0.4s';
      t.style.opacity = '0';
      setTimeout(function(){ try{ t.remove(); }catch(_){} }, 400);
    }, 1800);
  }
  window.showCreateLabModal = function(){
    openModal('createLabModal');
    const form = document.getElementById('createLabForm');
    if (!form) return;
    form.onsubmit = async function(e){
      e.preventDefault();
      const name = (document.getElementById('lab_name')?.value || '').trim();
      const location = (document.getElementById('lab_location')?.value || '').trim();
      const capacity = parseInt(document.getElementById('lab_capacity')?.value || '0', 10) || 0;
      const is_active = !!(document.getElementById('lab_active')?.checked);
      if (!name){ alert('Nombre del laboratorio es requerido'); return; }
      const submitBtn = form.querySelector('button[type="submit"]');
      const prevText = submitBtn ? submitBtn.textContent : '';
      if (submitBtn){ submitBtn.disabled = true; submitBtn.textContent = 'Creando...'; }
      const res = await apiPost('/api/admin/labs', { name, location, capacity, is_active });
      if (!res.ok){
        if (submitBtn){ submitBtn.disabled = false; submitBtn.textContent = prevText; }
        alert('Error al crear laboratorio: ' + (res.error || res.status));
        showToast('Error al crear laboratorio', 'error');
        return;
      }
      const m = document.getElementById('createLabModal'); if (m){ m.style.display='none'; m.setAttribute('aria-hidden','true'); }
      try { form.reset(); } catch(_){}
      // Guardar ID para resaltar
      window.__lastCreatedLabId = (Array.isArray(res.data) ? (res.data[0]?.id||null) : (res.data?.id||null));
      showToast('Laboratorio creado: ' + ((Array.isArray(res.data)?res.data[0]?.name:res.data?.name) || name), 'success');
      // Refrescar y navegar
      loadLabs();
      populateLabSelect('lab-action-select');
      populateLabSelect('computer_lab');
      populateLabSelect('group_lab');
      goAdminSection('labs');
      if (submitBtn){ submitBtn.disabled = false; submitBtn.textContent = prevText; }
    };
  };
  
  // Reemplazo: crear computadora usando modal y formulario
  window.showCreateComputerModal = function(){
    openModal('createComputerModal');
    populateLabSelect('computer_lab');
    const form = document.getElementById('createComputerForm');
    if (!form) return;
    form.onsubmit = async function(e){
      e.preventDefault();
      const name = (document.getElementById('computer_name')?.value || '').trim();
      const labId = parseInt(document.getElementById('computer_lab')?.value || '0', 10) || 0;
      const ip_address = (document.getElementById('computer_ip')?.value || '').trim();
      const mac_address = (document.getElementById('computer_mac')?.value || '').trim();
      const status = document.getElementById('computer_status')?.value || 'offline';
      if (!name){ alert('Nombre de la computadora es requerido'); return; }
      if (!labId){ alert('Seleccione un laboratorio'); return; }
      const submitBtn = form.querySelector('button[type="submit"]');
      const prevText = submitBtn ? submitBtn.textContent : '';
      if (submitBtn){ submitBtn.disabled = true; submitBtn.textContent = 'Creando...'; }
      const res = await apiPost('/api/admin/computers', { name, lab_id: labId, ip_address, mac_address, status });
      if (!res.ok){
        if (submitBtn){ submitBtn.disabled = false; submitBtn.textContent = prevText; }
        alert('Error al crear computadora: ' + (res.error || res.status));
        showToast('Error al crear computadora', 'error');
        return;
      }
      const m = document.getElementById('createComputerModal'); if (m){ m.style.display='none'; m.setAttribute('aria-hidden','true'); }
      try { form.reset(); } catch(_){}
      window.__lastCreatedComputerId = (Array.isArray(res.data) ? (res.data[0]?.id||null) : (res.data?.id||null));
      showToast('Computadora creada: ' + ((Array.isArray(res.data)?res.data[0]?.name:res.data?.name) || name), 'success');
      loadComputers('all');
      populateLabSelect('computer_lab');
      goAdminSection('computers');
      if (submitBtn){ submitBtn.disabled = false; submitBtn.textContent = prevText; }
    };
  };
  
  // Reemplazo: crear grupo usando modal y formulario
  window.showCreateGroupModal = function(){
    openModal('createGroupModal');
    populateTeacherSelect('group_teacher');
    populateLabSelect('group_lab');
    const form = document.getElementById('createGroupForm');
    if (!form) return;
    form.onsubmit = async function(e){
      e.preventDefault();
      const name = (document.getElementById('group_name')?.value || '').trim();
      const teacher_id = document.getElementById('group_teacher')?.value || '';
      const labId = parseInt(document.getElementById('group_lab')?.value || '0', 10) || 0;
      const is_active = !!(document.getElementById('group_active')?.checked);
      if (!name){ alert('Nombre del grupo es requerido'); return; }
      if (!teacher_id || String(teacher_id).length !== 36){ alert('Seleccione un profesor válido'); return; }
      if (!labId){ alert('Seleccione un laboratorio'); return; }
      const submitBtn = form.querySelector('button[type="submit"]');
      const prevText = submitBtn ? submitBtn.textContent : '';
      if (submitBtn){ submitBtn.disabled = true; submitBtn.textContent = 'Creando...'; }
      const res = await apiPost('/api/admin/groups', { name, teacher_id, lab_id: labId, is_active });
      if (!res.ok){
        if (submitBtn){ submitBtn.disabled = false; submitBtn.textContent = prevText; }
        alert('Error al crear grupo: ' + (res.error || res.status));
        showToast('Error al crear grupo', 'error');
        return;
      }
      const m = document.getElementById('createGroupModal'); if (m){ m.style.display='none'; m.setAttribute('aria-hidden','true'); }
      try { form.reset(); } catch(_){}
      // Refrescar listado y selects dependientes
      loadGroups();
      populateLabSelect('group_lab');
      populateTeacherSelect('group_teacher');
      goAdminSection('groups');
    };
  };

  // Acciones CRUD: editar/eliminar
  window.editLab = function(id){
    const l = (window.__labsCache||[]).find(x=>String(x.id)===String(id));
    if (!l){ alert('Laboratorio no encontrado'); return; }
    openModal('createLabModal');
    const form = document.getElementById('createLabForm');
    if (!form) return;
    document.getElementById('lab_name').value = l.name||'';
    document.getElementById('lab_location').value = l.location||'';
    document.getElementById('lab_capacity').value = String(l.capacity||0);
    document.getElementById('lab_active').checked = !!(l.is_active!==false);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn ? submitBtn.textContent : '';
    if (submitBtn) submitBtn.textContent = 'Guardar';
    form.onsubmit = async function(e){
      e.preventDefault();
      const name = (document.getElementById('lab_name')?.value || '').trim();
      const location = (document.getElementById('lab_location')?.value || '').trim();
      const capacity = parseInt(document.getElementById('lab_capacity')?.value || '0', 10) || 0;
      const is_active = !!(document.getElementById('lab_active')?.checked);
      const r = await apiPatch('/api/admin/labs/update', { id, name, location, capacity, is_active });
      if (!r.ok){ alert('Error al actualizar: '+(r.error||r.status)); return; }
      const m = document.getElementById('createLabModal'); if (m){ m.style.display='none'; m.setAttribute('aria-hidden','true'); }
      showToast('Laboratorio actualizado', 'success');
      if (submitBtn) submitBtn.textContent = originalText||'Crear';
      loadLabs();
    };
  };
  window.deleteLab = async function(id){
    if (!confirm('¿Eliminar este laboratorio?')) return;
    const r = await apiPatch('/api/admin/labs/delete', { id });
    if (!r.ok){ alert('Error al eliminar: '+(r.error||r.status)); return; }
    showToast('Laboratorio eliminado', 'success');
    loadLabs();
  };

  window.editComputer = async function(id){
    const c = (window.__computersCache||[]).find(x=>String(x.id)===String(id));
    if (!c){ alert('Computadora no encontrada'); return; }
    openModal('createComputerModal');
    await populateLabSelect('computer_lab');
    const form = document.getElementById('createComputerForm');
    if (!form) return;
    document.getElementById('computer_name').value = c.name||'';
    document.getElementById('computer_lab').value = String(c.lab_id||'');
    document.getElementById('computer_ip').value = c.ip_address||'';
    document.getElementById('computer_mac').value = c.mac_address||'';
    document.getElementById('computer_status').value = (c.status||'offline');
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn ? submitBtn.textContent : '';
    if (submitBtn) submitBtn.textContent = 'Guardar';
    form.onsubmit = async function(e){
      e.preventDefault();
      const payload = {
        id,
        name: (document.getElementById('computer_name')?.value || '').trim(),
        lab_id: parseInt(document.getElementById('computer_lab')?.value || '0', 10) || 0,
        ip_address: (document.getElementById('computer_ip')?.value || '').trim(),
        mac_address: (document.getElementById('computer_mac')?.value || '').trim(),
        status: document.getElementById('computer_status')?.value || 'offline'
      };
      const r = await apiPatch('/api/admin/computers/update', payload);
      if (!r.ok){ alert('Error al actualizar: '+(r.error||r.status)); return; }
      const m = document.getElementById('createComputerModal'); if (m){ m.style.display='none'; m.setAttribute('aria-hidden','true'); }
      showToast('Computadora actualizada', 'success');
      if (submitBtn) submitBtn.textContent = originalText||'Crear';
      loadComputers('all');
    };
  };
  window.deleteComputer = async function(id){
    if (!confirm('¿Eliminar esta computadora?')) return;
    const r = await apiPatch('/api/admin/computers/delete', { id });
    if (!r.ok){ alert('Error al eliminar: '+(r.error||r.status)); return; }
    showToast('Computadora eliminada', 'success');
    loadComputers('all');
  };

  window.editGroup = async function(id){
    const g = (window.__groupsCache||[]).find(x=>String(x.id)===String(id));
    if (!g){ alert('Grupo no encontrado'); return; }
    openModal('createGroupModal');
    await populateTeacherSelect('group_teacher');
    await populateLabSelect('group_lab');
    const form = document.getElementById('createGroupForm');
    if (!form) return;
    document.getElementById('group_name').value = g.name||'';
    document.getElementById('group_teacher').value = String(g.teacher_id||'');
    document.getElementById('group_lab').value = String(g.lab_id||'');
    document.getElementById('group_active').checked = !!(g.is_active!==false);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn ? submitBtn.textContent : '';
    if (submitBtn) submitBtn.textContent = 'Guardar';
    form.onsubmit = async function(e){
      e.preventDefault();
      const payload = {
        id,
        name: (document.getElementById('group_name')?.value || '').trim(),
        teacher_id: document.getElementById('group_teacher')?.value || '',
        lab_id: parseInt(document.getElementById('group_lab')?.value || '0', 10) || 0,
        is_active: !!(document.getElementById('group_active')?.checked)
      };
      const r = await apiPatch('/api/admin/groups/update', payload);
      if (!r.ok){ alert('Error al actualizar: '+(r.error||r.status)); return; }
      const m = document.getElementById('createGroupModal'); if (m){ m.style.display='none'; m.setAttribute('aria-hidden','true'); }
      showToast('Grupo actualizado', 'success');
      if (submitBtn) submitBtn.textContent = originalText||'Crear';
      loadGroups();
    };
  };
  window.deleteGroup = async function(id){
    if (!confirm('¿Eliminar este grupo?')) return;
    const r = await apiPatch('/api/admin/groups/delete', { id });
    if (!r.ok){ alert('Error al eliminar: '+(r.error||r.status)); return; }
    showToast('Grupo eliminado', 'success');
    loadGroups();
  };

  // Función Overview: actualiza contadores desde la API cuando está disponible
  var OVERVIEW_REFRESH_MS = 15000;
  var overviewTimer = null;
  function formatDateTime(ts){
    try { var d = new Date(ts); return d.toLocaleString(); }
    catch(e){ return String(ts||''); }
  }
  function formatRelative(ts){
    try {
      var d = new Date(ts);
      var diffSec = (Date.now() - d.getTime()) / 1000;
      if (!isFinite(diffSec) || diffSec < 0) return '';
      if (diffSec < 60) return 'hace ' + Math.floor(diffSec) + ' s';
      if (diffSec < 3600) return 'hace ' + Math.floor(diffSec/60) + ' min';
      if (diffSec < 86400) return 'hace ' + Math.floor(diffSec/3600) + ' h';
      return 'hace ' + Math.floor(diffSec/86400) + ' d';
    } catch(e){ return ''; }
  }
  async function loadOverview(){
    try {
      const [labs, comps, groups, sessions] = await Promise.all([
        apiGet('/api/admin/labs'),
        apiGet('/api/admin/computers'),
        apiGet('/api/admin/groups'),
        apiGet('/api/admin/sessions')
      ]);
      var el;
      if (labs.ok && (el=document.getElementById('total-labs'))) { el.textContent = Array.isArray(labs.data) ? labs.data.length : 0; }
      if (comps.ok) {
        var totalEl = document.getElementById('total-computers');
        var onlineEl = document.getElementById('online-computers');
        var list = Array.isArray(comps.data) ? comps.data : [];
        if (totalEl) totalEl.textContent = list.length;
        if (onlineEl) {
          var onlineCount = 0;
          list.forEach(function(c){
            var s = String(c.status||'').toLowerCase();
            if (s==='online' || s==='en_linea' || c.online === true) onlineCount++;
          });
          onlineEl.textContent = onlineCount;
        }
      }
      if (groups.ok && (el=document.getElementById('total-groups'))) { el.textContent = Array.isArray(groups.data) ? groups.data.length : 0; }
      var sessionsEl = document.getElementById('total-sessions');
      if (sessionsEl && sessions && sessions.ok) {
        var slist = Array.isArray(sessions.data) ? sessions.data : [];
        var activeList = slist.filter(function(s){ return !s.ended_at; });
        sessionsEl.textContent = activeList.length;
        var listEl = document.getElementById('active-sessions-list');
        if (listEl) {
          if (!activeList.length) {
            listEl.innerHTML = '<div class="empty-state">Sin sesiones activas</div>';
          } else {
            var html = '';
            activeList.slice(0, 10).forEach(function(s){
              var group = s.group_name || s.group || '-';
              var lab = s.lab_name || s.lab || '-';
              var teacher = s.teacher_name || s.teacher || '-';
              var start = formatDateTime(s.started_at);
              var rel = formatRelative(s.started_at);
              var status = s.status || 'activa';
              html += '<div class="session-item">'
                   +  '<span class="label">'+ group +'</span>'
                   +  '<span class="meta">'+ lab +'</span>'
                   +  '<span class="meta">'+ teacher +'</span>'
                   +  '<span class="meta">'+ start +'</span>'
                   +  (rel ? '<span class="meta">'+ rel +'</span>' : '')
                   +  '<span class="status">'+ status +'</span>'
                   +  '</div>';
            });
            listEl.innerHTML = html;
          }
        }
      }
    } catch(e) { /* silencioso: mantiene valores renderizados del servidor */ }
  }

  // Hook de navegación: cargar datos al mostrar secciones
  const originalShowSection = window.showSection;
  window.showSection = async function(sectionId){
    // Mostrar/ocultar secciones de forma consistente (incluye inline style)
    document.querySelectorAll('.admin-content section').forEach(function(s){
      var isTarget = (s.id === sectionId);
      s.classList.toggle('active', isTarget);
      s.style.display = isTarget ? 'block' : 'none';
    });
    // Sincronizar estado activo del menú
    document.querySelectorAll('.admin-nav .nav-item').forEach(function(n){
      n.classList.toggle('active', n.getAttribute('data-section') === sectionId);
    });
    try { history.replaceState(null, '', '#' + sectionId); } catch(_) {}

    if (sectionId === 'overview') {
      await loadOverview();
      if (overviewTimer) { clearInterval(overviewTimer); }
      overviewTimer = setInterval(loadOverview, OVERVIEW_REFRESH_MS);
    } else {
      if (overviewTimer) { clearInterval(overviewTimer); overviewTimer = null; }
    }
    if (sectionId === 'labs') { await loadLabs(); }
    if (sectionId === 'computers') { await loadComputers(); }
    if (sectionId === 'groups') { await loadGroups(); }
    if (sectionId === 'teachers') { await loadTeachers(); }
    if (sectionId === 'actions') { await loadActions(); }
    if (sectionId === 'sessions') { await loadSessions(); }
    if (sectionId === 'panel-ai-config') { await loadAIConfig(); }
  };