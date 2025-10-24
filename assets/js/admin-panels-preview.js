(function(){
  'use strict';
  async function read(url){ try{ const r = await fetch(url); if(!r.ok) return []; const d = await r.json(); return Array.isArray(d)?d:[]; }catch(e){ return []; } }
  function el(id){ return document.getElementById(id); }
  function downloadCSV(name, rows){ if(!rows||!rows.length){ alert('Sin datos para exportar'); return; } const headers = Array.from(rows.reduce((s,r)=>{ Object.keys(r).forEach(k=> s.add(k)); return s; }, new Set())); const csv = [headers.join(',')].concat(rows.map(r=> headers.map(h=> JSON.stringify(r[h]??'').replace(/,/g,';')).join(','))).join('\n'); const blob = new Blob([csv], { type:'text/csv;charset=utf-8;' }); const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = name; a.click(); setTimeout(()=> URL.revokeObjectURL(url), 1000); }

  async function init(){
    const labs = await read('/storage/labs.json');
    const computers = await read('/storage/computers.json');
    const groups = await read('/storage/groups.json');
    const sessions = await read('/storage/sessions.json');
    const users = await read('/storage/users.json');
    const grades = await read('/storage/grades.json');
    const exercises = await read('/storage/exercises.json');

    // Labs section
    (function(){ const sec = el('labs'); if(!sec) return; const byId = (l)=> l.id ?? l.lab_id ?? l.codigo ?? 'NA'; const compByLab = {}; computers.forEach(c=>{ const lid = c.lab_id ?? c.laboratorio_id ?? 'NA'; compByLab[lid] = (compByLab[lid]||[]).concat([c]); }); const grid = ['<div class="labs-status"><div class="cards-grid">']; labs.forEach(l=>{ const id = byId(l); const list = compByLab[id]||[]; const online = list.filter(x=> String(x.status||x.estado||'').toLowerCase().includes('online')).length; grid.push(`<div class="card"><h3>${l.name||l.nombre||('Lab '+id)}</h3><p>Equipos: ${list.length} · ✅ ${online}</p><div style="display:flex;gap:8px;flex-wrap:wrap;"><button class="btn btn-secondary" onclick="alert('Abrir detalles (preview)')">Detalles</button><button class="btn btn-secondary" onclick="alert('Programar mantenimiento (preview)')">Mantenimiento</button></div></div>`); }); if(!labs.length) grid.push('<p>No hay laboratorios.</p>'); grid.push('</div></div>'); sec.insertAdjacentHTML('beforeend', grid.join('')); })();

    // Computers section
    (function(){ const sec = el('computers'); if(!sec) return; const toolbar = document.createElement('div'); toolbar.style.marginBottom='12px'; toolbar.innerHTML = `<label style="margin-right:8px;">Laboratorio:</label><select id="filterLab"><option value="">Todos</option>${labs.map(l=>`<option value="${l.id??l.lab_id??l.codigo??''}">${l.name||l.nombre||('Lab '+(l.id??l.lab_id??l.codigo??''))}</option>`).join('')}</select><label style="margin:0 8px;">Estado:</label><select id="filterStatus"><option value="">Todos</option><option>online</option><option>offline</option><option>maintenance</option></select>`; sec.appendChild(toolbar); const list = document.createElement('div'); list.id='computers-list'; list.className='labs-status'; sec.appendChild(list); function render(){ const labSel = document.getElementById('filterLab').value||''; const stSel = (document.getElementById('filterStatus').value||'').toLowerCase(); const html = ['<div class="cards-grid">']; computers.filter(c=>{ const lid=c.lab_id??c.laboratorio_id??''; const st=String(c.status||c.estado||'').toLowerCase(); const okLab=!labSel||String(lid)===labSel; const okSt=!stSel||st.includes(stSel); return okLab&&okSt; }).forEach(c=>{ const st=String(c.status||c.estado||'').toLowerCase()||'offline'; html.push(`<div class="card"><h3>${c.name||c.nombre||('Equipo '+(c.id??''))}</h3><p>Lab: ${c.lab_id??c.laboratorio_id??'-'} · Estado: ${st}</p><div style="display:flex;gap:8px;flex-wrap:wrap;"><button class="btn btn-secondary" onclick="alert('Ping (preview)')">Ping</button><button class="btn btn-secondary" onclick="alert('Reiniciar (preview)')">Reiniciar</button></div></div>`); }); if(html.length===1) html.push('<p>Sin computadoras.</p>'); html.push('</div>'); list.innerHTML = html.join(''); } toolbar.addEventListener('change', render); render(); })();

    // Groups section
    (function(){ const sec = el('groups'); if(!sec) return; const wrap=document.createElement('div'); wrap.className='labs-status'; const html=['<div class="cards-grid">']; groups.forEach(g=>{ const size=(g.members||g.alumnos||[]).length; html.push(`<div class="card"><h3>${g.name||g.nombre||'Grupo'}</h3><p>Miembros: ${size}</p><div style="display:flex;gap:8px;flex-wrap:wrap;"><button class="btn btn-secondary" onclick="alert('Ver grupo (preview)')">Ver</button><button class="btn btn-secondary" onclick="alert('Exportar (preview)')">Exportar</button></div></div>`); }); if(!groups.length) html.push('<p>No hay grupos.</p>'); html.push('</div>'); wrap.innerHTML = html.join(''); sec.appendChild(wrap); })();

    // Teachers section
    (function(){ const sec = el('teachers'); if(!sec) return; const grid=document.createElement('div'); grid.className='role-users-grid'; (users||[]).filter(u=> /teacher|profesor|docente/i.test(String(u.role||u.rol||''))).forEach(u=>{ const card=document.createElement('div'); card.className='admin-user-card teacher-card'; card.innerHTML = `<div class="user-avatar">👨‍🏫</div><div class="user-info"><div class="user-name">${u.name||u.nombre||'Profesor'}</div><div class="user-email">${u.email||u.correo||''}</div><div class="user-group">${u.group||u.grupo||''}</div><div class="user-registered">${u.registeredAt||u.fecha_registro||''}</div></div><div class="user-actions"><button class="btn btn-secondary" onclick="alert('Ver perfil (preview)')">Ver</button><button class="btn btn-secondary" onclick="alert('Asignar (preview)')">Asignar</button></div>`; grid.appendChild(card); }); if(!grid.children.length){ grid.innerHTML = '<p>No hay profesores en users.json.</p>'; } sec.appendChild(grid); })();

    // Sessions section (list + filter)
    (function(){ const sec = el('sessions-section-container'); if(!sec) return; const toolbar=document.createElement('div'); toolbar.style.marginBottom='8px'; toolbar.innerHTML = `<label style="margin-right:8px;">Estado:</label><select id="filterSess"><option value="">Todas</option><option value="activa">Activas</option><option value="finalizada">Finalizadas</option></select>`; sec.appendChild(toolbar); const list=document.createElement('div'); sec.appendChild(list); function render(){ const f=(document.getElementById('filterSess').value||''); const target=sessions.filter(s=> f==='activa' ? (!s.ended_at && !s.fin) : f==='finalizada' ? (!!(s.ended_at||s.fin)) : true ); const html=['<div class="sessions-list"><ul>']; target.forEach(s=>{ const lab=s.lab_name||s.laboratorio||('Lab ' + (s.lab_id ?? '?')); const group=s.group_name||s.grupo||'Grupo'; const teacher=s.teacher_name||s.profesor||''; const started=s.started_at||s.inicio||''; const ended=s.ended_at||s.fin||''; html.push(`<li><strong>${lab}</strong> — ${group} — ${teacher} <div style="opacity:.7;">Inicio: ${started}${ended? ' · Fin: '+ended:''}</div></li>`); }); if(target.length===0) html.push('<li>Sin sesiones.</li>'); html.push('</ul></div>'); list.innerHTML = html.join(''); } toolbar.addEventListener('change', render); render(); })();

    // Users panel
    (function(){ const cont=el('users-panel-container'); if(!cont) return; const grid=document.createElement('div'); grid.className='role-users-grid'; (users||[]).forEach(u=>{ const role=String(u.role||u.rol||'student').toLowerCase(); const cls=role.includes('teacher')||role.includes('prof')? 'teacher-card' : role.includes('admin')? 'admin-card' : 'student-card'; const icon=role.includes('teacher')||role.includes('prof')? '👨‍🏫' : role.includes('admin')? '🛡️' : '👤'; const card=document.createElement('div'); card.className='admin-user-card '+cls; card.innerHTML = `<div class="user-avatar">${icon}</div><div class="user-info"><div class="user-name">${u.name||u.nombre||'Usuario'}</div><div class="user-email">${u.email||u.correo||''}</div><div class="user-group">${u.group||u.grupo||''}</div><div class="user-registered">${u.registeredAt||u.fecha_registro||''}</div></div><div class="user-actions"><button class="btn btn-secondary" onclick="alert('Editar (preview)')">Editar</button><button class="btn btn-secondary" onclick="alert('Desactivar (preview)')">Desactivar</button></div>`; grid.appendChild(card); }); if(!grid.children.length){ grid.innerHTML = '<p>No hay usuarios en users.json.</p>'; } cont.appendChild(grid); })();

    // Analytics panel
    (function(){ const cont=el('analytics-panel-container'); if(!cont) return; const labStats={}; labs.forEach(l=>{ const id=l.id??l.lab_id??l.codigo??'NA'; labStats[id]={ name:l.name||l.nombre||('Lab '+id), online:0, total:0 }; }); computers.forEach(c=>{ const lid=c.lab_id??c.laboratorio_id??'NA'; const st=String(c.status||c.estado||'offline').toLowerCase(); if(!labStats[lid]) labStats[lid]={ name:'Lab '+lid, online:0, total:0 }; labStats[lid].total++; if(st.includes('online')) labStats[lid].online++; }); const topLab = Object.values(labStats).sort((a,b)=> (b.online/(b.total||1)) - (a.online/(a.total||1)) )[0]; const activeCount = sessions.filter(s=> !s.ended_at && !s.fin).length; const byGroup={}; sessions.forEach(s=>{ const g=s.group_name||s.grupo||'Sin grupo'; byGroup[g]=(byGroup[g]||0)+1; }); const topGroup = Object.entries(byGroup).sort((a,b)=> b[1]-a[1])[0]; cont.innerHTML = `<div class="stats-grid"><div class="stat-card"><div class="stat-icon">🖥️</div><div class="stat-info"><div class="stat-value">${labs.length}</div><div class="stat-label">Laboratorios</div></div></div><div class="stat-card"><div class="stat-icon">💻</div><div class="stat-info"><div class="stat-value">${computers.length}</div><div class="stat-label">Computadoras</div></div></div><div class="stat-card"><div class="stat-icon">📅</div><div class="stat-info"><div class="stat-value">${activeCount}</div><div class="stat-label">Sesiones Activas</div></div></div><div class="stat-card"><div class="stat-icon">🏆</div><div class="stat-info"><div class="stat-value">${topLab? topLab.name : '-'}</div><div class="stat-label">Lab con más en línea</div></div></div></div><div class="labs-status" style="margin-top:12px;"><div class="cards-grid">${(topGroup? `<div class="card"><h3>Grupo más activo</h3><p>${topGroup[0]} (${topGroup[1]} sesiones)</p></div>` : '<div class="card"><h3>Grupos</h3><p>Sin datos</p></div>')}</div></div>`; })();

    // Notas panel
    (function(){ const cont=el('notas-panel-container'); if(!cont) return; if(!(grades&&grades.length)){ cont.innerHTML='<p>No hay datos de notas (grades.json).</p>'; return; } const html=['<div class="labs-status"><div class="cards-grid">']; grades.forEach(g=>{ html.push(`<div class="card"><h3>${g.student||g.alumno||'Estudiante'}</h3><p>Curso/Módulo: ${g.course||g.modulo||'-'}</p><p>Nota: <strong>${g.grade||g.nota||'-'}</strong> · Fecha: ${g.date||g.fecha||''}</p></div>`); }); html.push('</div></div>'); cont.innerHTML = html.join(''); })();

    // Exportación panel
    (function(){ const cont=el('export-panel-container'); if(!cont) return; cont.innerHTML = `<div style="display:flex;flex-wrap:wrap;gap:10px;"><button class="btn btn-secondary" onclick="window.__exp('labs')">Exportar Labs CSV</button><button class="btn btn-secondary" onclick="window.__exp('computers')">Exportar Computers CSV</button><button class="btn btn-secondary" onclick="window.__exp('groups')">Exportar Groups CSV</button><button class="btn btn-secondary" onclick="window.__exp('users')">Exportar Users CSV</button><button class="btn btn-secondary" onclick="window.__exp('sessions')">Exportar Sessions CSV</button></div>`; window.__exp = function(key){ const map={ labs, computers, groups, users, sessions }; downloadCSV(key+'.csv', map[key]||[]); }; })();

    // Config panel
    (function(){ const cont=el('config-panel-container'); if(!cont) return; cont.innerHTML = `<div style="display:flex;gap:10px;flex-wrap:wrap;"><button class="btn btn-secondary" onclick="(function(){var cur=document.body.getAttribute('data-theme')||'light';document.body.setAttribute('data-theme', cur==='light'?'dark':'light');})()">Alternar tema</button><button class="btn btn-secondary" onclick="localStorage.clear();alert('Cache local limpiada')">Limpiar cache local</button></div>`; })();

    // Códigos panel
    (function(){ const cont=el('codigos-panel-container'); if(!cont) return; if(!(exercises&&exercises.length)){ cont.innerHTML = '<p>No hay ejercicios en exercises.json.</p><button class="btn btn-secondary" onclick="alert(\'Nuevo ejercicio (preview)\')">Nuevo ejercicio</button>'; return; } const html=['<div class="labs-status"><div class="cards-grid">']; exercises.forEach(e=> html.push(`<div class="card"><h3>${e.title||e.titulo||'Ejercicio'}</h3><p>Dificultad: ${e.difficulty||e.dificultad||'-'}</p><button class="btn btn-secondary" onclick="alert('Editar (preview)')">Editar</button></div>`)); html.push('</div></div>'); cont.innerHTML = html.join(''); })();

    // Comunicación panel
    (function(){ const cont=el('comunicacion-panel-container'); if(!cont) return; cont.innerHTML = `<div style=\"display:flex;gap:8px;flex-wrap:wrap;margin-bottom:8px;\"><textarea id=\"broadcastMsg\" class=\"form-input\" placeholder=\"Escribe anuncio...\" style=\"min-width:280px;\"></textarea><button class=\"btn btn-secondary\" id=\"sendBroadcast\">Enviar</button></div><div id=\"broadcastList\"></div>`; const list=el('broadcastList'); const btn=el('sendBroadcast'); if(btn) btn.addEventListener('click', function(){ const t=(el('broadcastMsg').value||'').trim(); if(!t) return; const item=document.createElement('div'); item.className='notification-item'; item.innerHTML = '<span class=\"notification-icon\">📢</span><span class=\"notification-message\">'+t+'</span>'; list.prepend(item); el('broadcastMsg').value=''; }); })();

    // Analytics IA panel
    (function(){ const cont=el('analytics-ia-panel-container'); if(!cont) return; let status='offline'; try{ const hasReal = typeof window.SmartEducationSystem !== 'undefined'; const edu = hasReal ? new window.SmartEducationSystem(window.AI_CONFIG||{}) : null; status = edu && typeof edu.isOffline==='function' ? (edu.isOffline()? 'offline':'online') : 'offline'; }catch(_){} cont.innerHTML = `<div class=\"stats-grid\"><div class=\"stat-card\"><div class=\"stat-icon\">🤖</div><div class=\"stat-info\"><div class=\"stat-value\">${status.toUpperCase()}</div><div class=\"stat-label\">Estado del motor IA</div></div></div><div class=\"stat-card\"><div class=\"stat-icon\">📅</div><div class=\"stat-info\"><div class=\"stat-value\">${sessions.length}</div><div class=\"stat-label\">Sesiones totales</div></div></div></div>`; })();
  }
  // Run after DOM
  if(document.readyState==='loading'){ document.addEventListener('DOMContentLoaded', init); } else { init(); }
})();
// Start Code panel: generación de códigos especiales de registro
(function(){
  const cont = document.getElementById('start-code-panel-container');
  if(!cont) return;
  function load(){ try{ return JSON.parse(localStorage.getItem('startCodes'))||[]; }catch(e){ return []; } }
  function save(arr){ try{ localStorage.setItem('startCodes', JSON.stringify(arr)); }catch(e){} }
  async function tryPersist(arr){
    const el = document.getElementById('startPersistStatus');
    const detail = document.getElementById('startPersistDetail');
    try {
      const res = await fetch('/api/start-codes', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ codes: arr }) });
      if (el) el.textContent = res.ok ? 'online' : 'offline';
      if (detail) detail.textContent = res.ok ? '' : ('HTTP '+res.status+' '+(res.statusText||''));
    } catch(e) { if (el) el.textContent = 'offline'; if(detail) detail.textContent = 'error de red'; }
  }
  async function apiValidate(code){
    try{
      const r = await fetch('/api/start-codes/validate?code='+encodeURIComponent(code));
      if(!r.ok) return { valid:false, reason:'api_error' };
      return await r.json();
    }catch(e){ return { valid:false, reason:'offline' }; }
  }
  async function apiUse(code){
    try{
      const r = await fetch('/api/start-codes/use', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ code }) });
      return r.ok;
    }catch(e){ return false; }
  }
  async function checkOnline(){
    const el = document.getElementById('startPersistStatus');
    const detail = document.getElementById('startPersistDetail');
    const ts = new Date();
    try{
      const r = await fetch('/api/start-codes/ping');
      const ok = !!r.ok;
      if (el) el.textContent = ok ? 'online' : 'offline';
      if (detail) detail.textContent = ok ? ('✔ '+ts.toLocaleTimeString()) : ('HTTP '+r.status+' '+(r.statusText||'')+' · '+ts.toLocaleTimeString());
      return ok;
    }catch(e){
      if (el) el.textContent = 'offline';
      if (detail) detail.textContent = 'error de red · '+ts.toLocaleTimeString();
      return false;
    }
  }
  let __onlineInterval = 30000;
  let __onlineTimer = null;
  function scheduleOnline(){
    if(__onlineTimer) clearTimeout(__onlineTimer);
    __onlineTimer = setTimeout(async ()=>{
      const ok = await checkOnline();
      __onlineInterval = ok ? 60000 : Math.min(__onlineInterval*2, 300000);
      scheduleOnline();
    }, __onlineInterval);
  }
  function getConfig(){
    const tPref = (cont.querySelector('#startTeacherPrefix').value||'TEA').toUpperCase();
    const aPref = (cont.querySelector('#startAdminPrefix').value||'ADM').toUpperCase();
    const len = Math.max(4, parseInt(cont.querySelector('#startRandLen').value||'8', 10));
    const exp = (cont.querySelector('#startExpiry').value||'');
    return { tPref, aPref, len, exp };
  }
  function make(role, cfg){
    const alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    let rand=''; for(let i=0;i<cfg.len;i++){ rand += alphabet[Math.floor(Math.random()*alphabet.length)]; }
    const d = new Date(); const y = String(d.getFullYear()).slice(2); const m = String(d.getMonth()+1).padStart(2,'0'); const day = String(d.getDate()).padStart(2,'0');
    const pref = role==='admin' ? cfg.aPref : cfg.tPref;
    return `${pref}-${y}${m}${day}-${rand}`;
  }
  function isExpired(item){
    const exp = item.expires_at; if(!exp) return false; const t = new Date(exp).getTime(); return isFinite(t) && Date.now() > t;
  }
  function validateFormat(code, cfg){
    const re = new RegExp(`^([A-Z]{3,5})-\\d{6}-[A-Z0-9]{${cfg.len}}$`);
    return re.test(code);
  }
  function showQR(code, target){
    const data = encodeURIComponent('algorix://register?code='+code);
    const url = `https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=${data}`;
    const img = document.createElement('img'); img.src = url; img.alt = 'QR'; img.style.border='1px solid var(--border-color)'; img.style.borderRadius='8px'; img.style.marginTop='8px';
    target.parentNode.appendChild(img);
  }
  function render(){
    const items = load();
    const list = cont.querySelector('#startCodesList');
    if(!list) return;
    if(!items.length){ list.innerHTML = '<p>No hay códigos generados aún.</p>'; return; }
    const html = ['<div class="labs-status"><div class="cards-grid">'];
    items.forEach(it=>{
      const roleName = it.role==='admin' ? 'Administrador' : 'Profesor';
      const state = isExpired(it) ? 'expirado' : (it.used ? 'usado' : 'disponible');
      html.push(`<div class="card"><h3>${roleName}</h3><p><code>${it.code}</code></p><p>Expira: ${it.expires_at || '—'} · Estado: ${state}</p><div style="display:flex;gap:8px;flex-wrap:wrap;"><button class="btn btn-secondary" data-copy="${it.code}">Copiar</button><button class="btn btn-secondary" data-qr="${it.code}">QR</button><button class="btn btn-secondary" data-use="${it.code}">Marcar usado</button></div></div>`);
    });
    html.push('</div></div>');
    list.innerHTML = html.join('');
    list.querySelectorAll('button[data-copy]').forEach(b=>{ b.addEventListener('click', ()=> { const txt=b.getAttribute('data-copy'); if(navigator.clipboard){ navigator.clipboard.writeText(txt); } }); });
    list.querySelectorAll('button[data-qr]').forEach(b=>{ b.addEventListener('click', ()=> showQR(b.getAttribute('data-qr'), b)); });
    list.querySelectorAll('button[data-use]').forEach(b=>{ b.addEventListener('click', async ()=> { const code=b.getAttribute('data-use'); const arr = load(); const idx = arr.findIndex(x=> x.code===code); if(idx>=0){ const online = (document.getElementById('startPersistStatus')?.textContent==='online'); if(online){ const ok = await apiUse(code); if(!ok){ alert('No se pudo marcar como usado en el servidor.'); return; } } arr[idx].used = true; save(arr); render(); if(online){ tryPersist(load()); } } }); });
  }
  cont.innerHTML = `<div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:8px;">
    <label>Prefijo Profesores</label><input type="text" id="startTeacherPrefix" class="form-input" value="TEA" style="max-width:100px;">
    <label>Prefijo Admins</label><input type="text" id="startAdminPrefix" class="form-input" value="ADM" style="max-width:100px;">
    <label>Longitud</label><input type="number" id="startRandLen" class="form-input" min="4" value="8" style="max-width:90px;">
    <label>Expira</label><input type="datetime-local" id="startExpiry" class="form-input" style="max-width:220px;">
    <span style="margin-left:auto;">persistencia: <strong id="startPersistStatus">preview</strong> <button class="btn btn-secondary" id="retryPersist" style="margin-left:8px;">Reintentar</button> <span id="startPersistDetail" style="margin-left:8px;opacity:.7;"></span></span>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:8px;">
    <label>Cantidad</label><input type="number" id="startQty" class="form-input" min="1" value="5" style="max-width:100px;">
    <label class="form-checkbox"><input type="checkbox" id="roleTeachers" checked> Profesores</label>
    <label class="form-checkbox"><input type="checkbox" id="roleAdmins" checked> Administradores</label>
    <button class="btn btn-secondary" id="generateStartCodes">Generar</button>
    <button class="btn btn-secondary" id="exportStartCodes">Exportar CSV</button>
    <button class="btn btn-secondary" id="clearStartCodes">Limpiar</button>
  </div>
  <div class="card" style="margin-bottom:8px; padding:12px; border:1px solid var(--border-color); border-radius:8px; background:var(--bg-secondary);">
    <label>Validar código</label>
    <input type="text" id="startValidateInput" class="form-input" placeholder="Pega un código para validar" style="max-width:320px;">
    <button class="btn btn-secondary" id="startValidateBtn">Validar</button>
    <span id="startValidateResult" style="margin-left:8px;"></span>
  </div>
  <div id="startCodesList"></div>`;
  cont.querySelector('#generateStartCodes').addEventListener('click', async ()=>{
    const qty = Math.max(1, parseInt(cont.querySelector('#startQty').value||'5',10));
    const doTeachers = !!cont.querySelector('#roleTeachers').checked;
    const doAdmins = !!cont.querySelector('#roleAdmins').checked;
    const cfg = getConfig();
    const buff = load();
    for(let i=0;i<qty;i++){
      if(doTeachers){ const code = make('teacher', cfg); buff.push({ role:'teacher', code, created_at: new Date().toISOString(), expires_at: cfg.exp||'', used:false }); }
      if(doAdmins){ const code = make('admin', cfg); buff.push({ role:'admin', code, created_at: new Date().toISOString(), expires_at: cfg.exp||'', used:false }); }
    }
    save(buff); render(); tryPersist(buff);
  });
  cont.querySelector('#exportStartCodes').addEventListener('click', ()=>{
    const items = load();
    if(!items.length) return alert('No hay códigos para exportar.');
    const csv = ['role,code,created_at,expires_at,used'].concat(items.map(i=>[i.role,i.code,i.created_at,i.expires_at||'',i.used?1:0].join(','))).join('\n');
    const blob = new Blob([csv], {type:'text/csv'}); const url = URL.createObjectURL(blob);
    const a = document.createElement('a'); a.href = url; a.download = 'start-codes.csv'; a.click(); setTimeout(()=>URL.revokeObjectURL(url), 1000);
  });
  cont.querySelector('#clearStartCodes').addEventListener('click', ()=>{ save([]); render(); tryPersist([]); });
  const rb = cont.querySelector('#retryPersist'); if(rb) rb.addEventListener('click', checkOnline);
  cont.querySelector('#startValidateBtn').addEventListener('click', async ()=>{
    const cfg = getConfig();
    const code = (cont.querySelector('#startValidateInput').value||'').trim().toUpperCase();
    const arr = load(); const found = arr.find(x=> x.code===code);
    const resEl = document.getElementById('startValidateResult');
    if(!validateFormat(code, cfg)){ resEl.textContent='Formato inválido'; resEl.style.color='var(--danger-color)'; return; }
    const online = (document.getElementById('startPersistStatus')?.textContent==='online');
    if(online){
      const r = await apiValidate(code);
      if(!r.valid){
        const reasonMap = { api_error: 'Error API', offline: 'Offline', expired: 'Código expirado', used: 'Código ya usado', not_found: 'No encontrado' };
        resEl.textContent = reasonMap[r.reason] || 'No válido';
        resEl.style.color='var(--danger-color)';
        return;
      }
      resEl.textContent = 'Código válido (servidor)';
      resEl.style.color='var(--success-color)';
      return;
    }
    if(found && found.used){ resEl.textContent='Código ya usado'; resEl.style.color='var(--danger-color)'; return; }
    if(found && isExpired(found)){ resEl.textContent='Código expirado'; resEl.style.color='var(--danger-color)'; return; }
    resEl.textContent = found ? 'Código válido' : 'Válido (no registrado en esta instancia)';
    resEl.style.color='var(--success-color)';
  });
  render();
  checkOnline();
  scheduleOnline();
})();