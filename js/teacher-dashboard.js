// Teacher Dashboard JS
(function(){
  const sections = {
    'main-panel': document.getElementById('main-panel'),
    'my-groups': document.getElementById('my-groups'),
    'lab-control': document.getElementById('lab-control'),
    'assignments': document.getElementById('assignments'),
    'sessions': document.getElementById('sessions'),
    'users': document.getElementById('users'),
    'analytics': document.getElementById('analytics'),
    'grades': document.getElementById('grades'),
    'export': document.getElementById('export'),
    'code-management': document.getElementById('code-management'),
    'communications': document.getElementById('communications')
  };

  function showSection(id){
    Object.values(sections).forEach(s => s && s.classList.remove('active'));
    const el = sections[id];
    if(el){ el.classList.add('active'); }
    updateActiveNav(id);
    // Cargar datos desde la base de datos al cambiar de sección
    if (id === 'lab-control') {
      populateTeacherLabSelect();
    }
    if (id === 'assignments') {
      populateAssignmentGroupFilter();
    }
    if (id === 'sessions') {
      if (typeof window.loadTeacherSessions === 'function') {
        window.loadTeacherSessions();
      }
    }
    if (id === 'grades') {
      if (typeof window.populateGradeCourses === 'function') {
        window.populateGradeCourses();
      }
    }
  }

  function updateActiveNav(id){
    document.querySelectorAll('.teacher-nav .nav-item').forEach(a => {
      if(a.dataset.section === id){
        a.classList.add('active');
      }else{
        a.classList.remove('active');
      }
    });
  }

  function initNav(){
    document.querySelectorAll('.teacher-nav .nav-item').forEach(a => {
      a.addEventListener('click', (e) => {
        e.preventDefault();
        const id = a.dataset.section;
        showSection(id);
      });
    });
  }

  // Carga de estudiantes desde API
  async function loadStudents(){
    const tbody = document.getElementById('students-tbody');
    if(!tbody) return;
    tbody.innerHTML = '<tr><td colspan="3">Cargando...</td></tr>';
    try {
      const res = await fetch('/api/teacher/users', { headers: { 'Accept': 'application/json' }});
      if(!res.ok) throw new Error('HTTP '+res.status);
      const json = await res.json();
      const students = Array.isArray(json.data) ? json.data : [];
      tbody.innerHTML = '';
      students.forEach(s => {
        const tr = document.createElement('tr');
        const role = (s.role === 'student' || s.role === 'estudiante') ? 'estudiante' : (s.role || '');
        tr.innerHTML = `<td>${s.name || ''}</td><td>${s.email || ''}</td><td>${role}</td>`;
        tbody.appendChild(tr);
      });
      if(students.length === 0){ tbody.innerHTML = '<tr><td colspan="3">Sin estudiantes</td></tr>'; }
    } catch (e) {
      tbody.innerHTML = '<tr><td colspan="3">Error al cargar</td></tr>';
      console.error('loadStudents error', e);
    }
  }

  function bindStudents(){
    const btn = document.getElementById('btn-load-students');
    if(btn){ btn.addEventListener('click', loadStudents); }
  }

  // Métricas desde API
  async function updateAnalytics(){
    const mtStudents = document.getElementById('metric-students');
    const mtTeachers = document.getElementById('metric-teachers');
    const mtSessions = document.getElementById('metric-sessions');
    if(mtStudents) mtStudents.textContent = '…';
    if(mtTeachers) mtTeachers.textContent = '…';
    if(mtSessions) mtSessions.textContent = '…';
    try {
      const res = await fetch('/api/teacher/analytics', { headers: { 'Accept': 'application/json' }});
      if(!res.ok) throw new Error('HTTP '+res.status);
      const json = await res.json();
      const d = json.data || {};
      if(mtStudents) mtStudents.textContent = d.students_count ?? '-';
      if(mtTeachers) mtTeachers.textContent = d.teachers_count ?? '-';
      if(mtSessions) mtSessions.textContent = d.sessions_count ?? '-';
    } catch (e) {
      console.error('updateAnalytics error', e);
    }
  }

  // Exportación CSV desde tablas existentes
  function tableToCSV(tableSel){
    const table = document.querySelector(tableSel);
    if(!table) return '';
    const rows = Array.from(table.querySelectorAll('tr'));
    return rows.map(row => Array.from(row.children).map(td => '"'+td.textContent.replace(/\n/g,' ').trim()+'"').join(',')).join('\n');
  }

  function downloadCSV(filename, csv){
    const blob = new Blob([csv], {type: 'text/csv;charset=utf-8;'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  }

  function exportAnalyticsPDF(){
    const section = document.getElementById('analytics');
    if(!section){ alert('Sección Analytics no disponible'); return; }
    const w = window.open('', '_blank');
    const html = `<!doctype html><html><head><meta charset="utf-8"><title>Analytics</title>
      <style>body{font-family:Arial,sans-serif;padding:20px} .cards-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px} .card{border:1px solid #ccc;border-radius:8px;padding:12px}</style></head><body>
      <h1>Analytics</h1>${section.querySelector('.cards-grid')?.outerHTML || section.outerHTML}
      </body></html>`;
    w.document.write(html);
    w.document.close(); w.focus();
    w.print();
    w.close();
  }

  function bindExport(){
    const btnA = document.getElementById('btn-export-assignments');
    const btnS = document.getElementById('btn-export-sessions');
    const btnP = document.getElementById('btn-export-pdf');
    if(btnA){
      btnA.addEventListener('click', () => {
        const csv = tableToCSV('#assignments table');
        downloadCSV('asignaciones.csv', csv || '');
      });
    }
    if(btnS){
      btnS.addEventListener('click', () => {
        const csv = tableToCSV('#sessions table');
        downloadCSV('historial.csv', csv || '');
      });
    }
    if(btnP){ btnP.addEventListener('click', exportAnalyticsPDF); }
  }

  // Comunicación vía API
  async function loadCommList(){
    const list = document.getElementById('comm-list');
    if(!list) return;
    list.innerHTML = '';
    try {
      const res = await fetch('/api/teacher/communications', { headers: { 'Accept': 'application/json' }});
      if(!res.ok) throw new Error('HTTP '+res.status);
      const json = await res.json();
      const msgs = Array.isArray(json.data) ? json.data : [];
      msgs.forEach(m => {
        const li = document.createElement('li');
        const date = new Date(m.created_at || Date.now()).toLocaleString();
        const author = m.author_role ? ` (${m.author_role})` : '';
        li.textContent = `${date}${author}: ${m.message}`;
        list.appendChild(li);
      });
      if(msgs.length === 0){ const li = document.createElement('li'); li.textContent = 'No hay anuncios publicados.'; list.appendChild(li); }
    } catch (e) {
      console.error('loadCommList error', e);
    }
  }

  function bindComm(){
    const input = document.getElementById('comm-message');
    const btnSend = document.getElementById('btn-comm-send');
    const btnClear = document.getElementById('btn-comm-clear');
    const list = document.getElementById('comm-list');
    if(btnSend && input && list){
      btnSend.addEventListener('click', async () => {
        const msg = input.value.trim();
        if(!msg) return;
        try {
          const res = await fetch('/api/teacher/communications', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-Token': (window.CSRF_TOKEN || '')
            },
            body: JSON.stringify({ message: msg })
          });
          if(!res.ok) throw new Error('HTTP '+res.status);
          input.value = '';
          await loadCommList();
        } catch (e) {
          alert('Error al publicar el anuncio');
          console.error('comm send error', e);
        }
      });
    }
    if(btnClear && list){
      btnClear.addEventListener('click', () => { list.innerHTML = ''; });
    }
    // Cargar listado inicial
    loadCommList();
  }

  // Mantener funcionalidad previa de gestión de códigos
  function bindCodeTools(){
    const btnChallenge = document.getElementById('btn-gen-challenge');
    const btnHint = document.getElementById('btn-gen-hint');
    const btnEval = document.getElementById('btn-evaluate');
    const code = document.getElementById('teacher-code');
    const lang = document.getElementById('teacher-lang');
    const diff = document.getElementById('teacher-diff');

    function toast(msg){
      alert(msg);
    }

    if(btnChallenge){
      btnChallenge.addEventListener('click', () => {
        toast(`Ejercicio generado para ${lang.value} [${diff.value}].`);
      });
    }
    if(btnHint){
      btnHint.addEventListener('click', () => {
        toast('Pista generada según el código pegado.');
      });
    }
    if(btnEval){
      btnEval.addEventListener('click', () => {
        const hasCode = (code && code.value.trim().length > 0);
        toast(hasCode ? 'Código evaluado con éxito.' : 'Pega tu código para evaluar.');
      });
    }
  }

  function init(){
    initNav();
    bindStudents();
    bindExport();
    bindComm();
    bindCodeTools();
    updateAnalytics();
  }

  document.addEventListener('DOMContentLoaded', init);
})();
(function(){
  // Popular select de laboratorios en el panel de profesor
  async function populateTeacherLabSelect(){
    const sel = document.getElementById('teacher-lab-select');
    if (!sel) return;
    sel.innerHTML = '<option value="">Seleccione un laboratorio</option>';
    try {
      const res = await fetch('/api/teacher/labs', { headers: { 'Accept': 'application/json' } });
      const j = await res.json();
      const labs = j.data || [];
      labs.forEach(l => {
        const opt = document.createElement('option');
        opt.value = l.id;
        opt.textContent = l.name || ('Lab ' + l.id);
        sel.appendChild(opt);
      });
    } catch (e) {
      console.error('populateTeacherLabSelect error', e);
    }
  }

  // Exponer para que showSection pueda usarla
  window.populateTeacherLabSelect = populateTeacherLabSelect;

  // Cargar al inicio si el select existe
  document.addEventListener('DOMContentLoaded', function(){
    populateTeacherLabSelect();
  });
})();
(function(){
  // Popular filtro de asignaciones con grupos del profesor
  async function populateAssignmentGroupFilter(){
    const sel = document.getElementById('assignment-group-filter');
    if (!sel) return;
    sel.innerHTML = '<option value="all">Todos los grupos</option>';
    try {
      const res = await fetch('/api/teacher/groups', { headers: { 'Accept': 'application/json' } });
      const j = await res.json();
      const groups = j.data || [];
      groups.forEach(g => {
        const opt = document.createElement('option');
        opt.value = g.id;
        opt.textContent = g.name || ('Grupo ' + g.id);
        sel.appendChild(opt);
      });
    } catch (e) {
      console.error('populateAssignmentGroupFilter error', e);
    }
  }
  window.populateAssignmentGroupFilter = populateAssignmentGroupFilter;
  // Evitar error si filterAssignments no existe todavía
  if (typeof window.filterAssignments !== 'function') {
    window.filterAssignments = function(){
      // Punto de extensión: aquí se aplicaría el filtro al listado de asignaciones
      console.log('filterAssignments:', document.getElementById('assignment-group-filter')?.value || 'all');
    };
  }
  document.addEventListener('DOMContentLoaded', function(){
    populateAssignmentGroupFilter();
  });
})();
(function(){
  const usersSection = document.getElementById('users-section');
  if (!usersSection) return;

  const usersTbody = document.querySelector('#users-table tbody');
  const qInput = document.getElementById('filter-q');
  const roleSelect = document.getElementById('filter-role');
  const groupSelect = document.getElementById('filter-group');
  const estadoSelect = document.getElementById('filter-estado');
  const applyBtn = document.getElementById('apply-filters');

  async function loadGroups() {
    try {
      const res = await fetch('/api/teacher/groups', { headers: { 'Accept': 'application/json' } });
      const j = await res.json();
      const groups = j.data || [];
      if (groupSelect) {
        groupSelect.innerHTML = '<option value="">Todos los grupos</option>' + groups.map(g => `<option value="${g.id}">${g.name}</option>`).join('');
      }
    } catch (e) { console.error('loadGroups error', e); }
  }

  async function loadUsers() {
    const params = new URLSearchParams();
    const q = (qInput?.value || '').trim(); if (q) params.set('q', q);
    const role = roleSelect?.value || 'student'; if (role) params.set('role', role);
    const gid = groupSelect?.value || ''; if (gid) params.set('group_id', gid);
    const est = estadoSelect?.value || ''; if (est) params.set('estado', est);
    params.set('limit', '100');
    const url = '/api/teacher/users?' + params.toString();
    try {
      const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
      const j = await res.json();
      const users = j.data || [];
      if (usersTbody) {
        usersTbody.innerHTML = users.map(u => `<tr><td>${u.id}</td><td>${u.name||''}</td><td>${u.email||''}</td><td>${u.role||''}</td></tr>`).join('');
      }
    } catch (e) { console.error('loadUsers error', e); }
  }

  if (applyBtn) { applyBtn.addEventListener('click', loadUsers); }
  loadGroups().then(loadUsers);
})();
(function(){
  async function loadTeacherSessions(){
    const tbody = document.getElementById('teacher-sessions-tbody');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="7">Cargando...</td></tr>';
    try {
      const [sessRes, labsRes, groupsRes] = await Promise.all([
        fetch('/api/teacher/sessions', { headers: { 'Accept': 'application/json' } }),
        fetch('/api/teacher/labs', { headers: { 'Accept': 'application/json' } }),
        fetch('/api/teacher/groups', { headers: { 'Accept': 'application/json' } })
      ]);
      const [sessJ, labsJ, groupsJ] = await Promise.all([sessRes.json(), labsRes.json(), groupsRes.json()]);
      const sessions = Array.isArray(sessJ.data) ? sessJ.data : [];
      const labs = Array.isArray(labsJ.data) ? labsJ.data : [];
      const groups = Array.isArray(groupsJ.data) ? groupsJ.data : [];
      const labMap = {}; labs.forEach(l => { labMap[l.id] = l.name || ('Lab ' + l.id); });
      const groupMap = {}; groups.forEach(g => { groupMap[g.id] = g.name || ('Grupo ' + g.id); });
      tbody.innerHTML = sessions.map(s => {
        const started = s.started_at ? new Date(s.started_at) : null;
        const ended = s.ended_at ? new Date(s.ended_at) : null;
        const fecha = started ? started.toLocaleDateString() : '';
        const inicio = started ? started.toLocaleTimeString() : '';
        const fin = ended ? ended.toLocaleTimeString() : '';
        let dur = '';
        if (started && ended) {
          const ms = ended - started;
          const mins = Math.round(ms / 60000);
          dur = mins + ' min';
        }
        const labName = labMap[s.lab_id] || s.lab_id || '';
        const groupName = groupMap[s.group_id] || s.group_id || '';
        const notas = s.notes || '';
        return `<tr><td>${fecha}</td><td>${labName}</td><td>${groupName}</td><td>${inicio}</td><td>${fin}</td><td>${dur}</td><td>${notas}</td></tr>`;
      }).join('');
      if (sessions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7">Sin sesiones registradas</td></tr>';
      }
    } catch (e) {
      tbody.innerHTML = '<tr><td colspan="7">Error al cargar</td></tr>';
      console.error('loadTeacherSessions error', e);
    }
  }
  window.loadTeacherSessions = loadTeacherSessions;
})();
(function(){
  // Cursos, ejercicios y notas
  async function populateGradeCourses(){
    const courseSel = document.getElementById('grade-course-select');
    if (!courseSel) return;
    courseSel.innerHTML = '<option value="">Seleccione...</option>';
    try {
      const res = await fetch('/api/teacher/courses', { headers: { 'Accept': 'application/json' } });
      const j = await res.json();
      const courses = j.data || [];
      courses.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.id;
        opt.textContent = c.title || ('Curso ' + c.id);
        courseSel.appendChild(opt);
      });
      // Autoseleccionar el primero si no hay valor
      if (!courseSel.value && courses.length > 0) {
        courseSel.value = String(courses[0].id);
      }
    } catch (e) { console.error('populateGradeCourses error', e); }
    const cid = courseSel.value || '';
    if (cid) { await populateGradeExercises(cid); }
  }

  async function populateGradeExercises(courseId){
    const exSel = document.getElementById('grade-exercise-select');
    if (!exSel) return;
    exSel.innerHTML = '<option value="">Todos</option>';
    if (!courseId) return;
    try {
      const res = await fetch(`/api/teacher/exercises?course_id=${encodeURIComponent(courseId)}`, { headers: { 'Accept': 'application/json' } });
      const j = await res.json();
      const exercises = j.data || [];
      exercises.forEach(e => {
        const opt = document.createElement('option');
        opt.value = e.id;
        opt.textContent = e.title || ('Ejercicio ' + e.id);
        exSel.appendChild(opt);
      });
      // Autoseleccionar el primer ejercicio si existe
      if (exSel && !exSel.value && exercises.length > 0) {
        exSel.value = String(exercises[0].id);
      }
    } catch (e) { console.error('populateGradeExercises error', e); }
  }

  function computeStats(values){
    const nums = values.filter(v => typeof v === 'number' && !isNaN(v));
    const count = nums.length;
    const avg = count ? (nums.reduce((a,b)=>a+b,0)/count) : null;
    const min = count ? Math.min(...nums) : null;
    const max = count ? Math.max(...nums) : null;
    return { count, avg, min, max };
  }

  function showCrudMessage(type, text){
    const el = document.getElementById('grade-crud-msg');
    const msg = (text || '').toString();
    if (el) {
      el.textContent = msg;
      el.className = 'crud-msg ' + (type || 'info');
    } else {
      if (type === 'error') alert(msg || 'Error'); else alert(msg || 'OK');
    }
  }

  async function loadGrades(){
    const courseSel = document.getElementById('grade-course-select');
    const exSel = document.getElementById('grade-exercise-select');
    const tbody = document.getElementById('grades-tbody');
    const avgEl = document.getElementById('grade-avg');
    const minEl = document.getElementById('grade-min');
    const maxEl = document.getElementById('grade-max');
    const countEl = document.getElementById('grade-count');
    if (!courseSel || !tbody) return;

    const courseId = courseSel.value || '';
    const exerciseId = exSel?.value || '';
    if (!courseId) { tbody.innerHTML = '<tr><td colspan="4">Seleccione un curso</td></tr>'; return; }
    tbody.innerHTML = '<tr><td colspan="4">Cargando...</td></tr>';

    let url = '';
    if (exerciseId) { url = `/api/teacher/grades?exercise_id=${encodeURIComponent(exerciseId)}`; }
    else { url = `/api/teacher/grades?course_id=${encodeURIComponent(courseId)}`; }

    try {
      const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
      const j = await res.json();
      const rows = Array.isArray(j.data) ? j.data : [];
      let values = [];
      tbody.innerHTML = rows.map(r => {
        const alumno = r.user_id || r.student_id || '-';
        let elemento = '';
        let calif = null;
        let updated = r.updated_at || r.created_at || '';
        if (exerciseId) {
          elemento = `Ejercicio ${r.exercise_id || exerciseId}`;
          calif = (typeof r.grade === 'number' ? r.grade : (typeof r.score === 'number' ? r.score : null));
        } else {
          elemento = r.module_id ? `Módulo ${r.module_id}` : `Curso ${r.course_id || courseId}`;
          calif = (typeof r.final_grade === 'number' ? r.final_grade : null);
        }
        if (typeof calif === 'number') values.push(calif);
        const califText = (calif === null ? '-' : calif.toFixed(2));
        const updText = updated ? new Date(updated).toLocaleString() : '';
        const gradeId = r.id ?? '';
        const courseData = r.course_id ?? courseId ?? '';
        const moduleData = r.module_id ?? '';
        const exerciseData = exerciseId || r.exercise_id || '';
        const finalData = (calif === null ? '' : String(calif));
        return `<tr data-id="${gradeId}" data-user="${alumno}" data-course="${courseData}" data-module="${moduleData}" data-exercise="${exerciseData}" data-final="${finalData}"><td>${alumno}</td><td>${elemento}</td><td>${califText}</td><td>${updText}</td></tr>`;
      }).join('');
      // Interacción: clic en fila rellena el formulario y resalta selección
      const idEl = document.getElementById('grade-crud-id');
      const userIdEl = document.getElementById('grade-crud-user');
      const moduleEl = document.getElementById('grade-crud-module');
      const finalEl = document.getElementById('grade-crud-final');
      let lastSelected = null;
      tbody.querySelectorAll('tr').forEach(tr => {
        tr.addEventListener('click', () => {
          if (lastSelected) { lastSelected.style.backgroundColor = ''; }
          tr.style.backgroundColor = 'rgba(80,160,250,0.15)';
          lastSelected = tr;
          if (idEl) idEl.value = tr.dataset.id || '';
          if (userIdEl) userIdEl.value = tr.dataset.user || '';
          if (moduleEl) moduleEl.value = tr.dataset.module || '';
          if (finalEl) finalEl.value = tr.dataset.final || '';
          if (courseSel && tr.dataset.course) courseSel.value = tr.dataset.course;
          if (exSel && tr.dataset.exercise) exSel.value = tr.dataset.exercise;
        });
      });
      if (rows.length === 0) { tbody.innerHTML = '<tr><td colspan="4">Sin datos de calificaciones</td></tr>'; }
      const stats = computeStats(values);
      if (avgEl) avgEl.textContent = (stats.avg == null ? '—' : stats.avg.toFixed(2));
      if (minEl) minEl.textContent = (stats.min == null ? '—' : stats.min.toFixed(2));
      if (maxEl) maxEl.textContent = (stats.max == null ? '—' : stats.max.toFixed(2));
      if (countEl) countEl.textContent = String(stats.count);
    } catch (e) {
      console.error('loadGrades error', e);
      tbody.innerHTML = '<tr><td colspan="4">Error al cargar</td></tr>';
    }
  }

  // CRUD y validaciones
  async function apiRequest(method, url, body){
    try {
      const headers = { 'Content-Type': 'application/json', 'Accept':'application/json' };
      if (window.CSRF_TOKEN) headers['X-CSRF-Token'] = window.CSRF_TOKEN;
      const opts = { method, headers };
      if (body && method !== 'DELETE') { opts.body = JSON.stringify(body); }
      const res = await fetch(url, opts);
      const j = await res.json().catch(()=>({}));
      return { ok: res.ok, status: res.status, data: j.data || null, error: j.error || null };
    } catch(e){ return { ok:false, status:0, data:null, error:e?.message||'network' }; }
  }

  function validateFinalGrade(value){
    const n = Number(value);
    if (Number.isNaN(n)) return { ok:false, msg:'Ingrese una calificación válida' };
    if (n < 0 || n > 100) return { ok:false, msg:'La calificación debe estar entre 0 y 100' };
    return { ok:true, val:n };
  }

  async function createGrade(){
    const courseSel = document.getElementById('grade-course-select');
    const userIdEl = document.getElementById('grade-crud-user');
    const moduleEl = document.getElementById('grade-crud-module');
    const finalEl = document.getElementById('grade-crud-final');
    if (!courseSel || !userIdEl || !finalEl) return;
    const courseId = courseSel.value || '';
    const userId = (userIdEl.value||'').trim();
    const vf = validateFinalGrade(finalEl.value||'');
    if (!courseId || !userId) { showCrudMessage('error','Curso y alumno son obligatorios'); return; }
    if (!vf.ok) { showCrudMessage('error', vf.msg); return; }
    const moduleId = (moduleEl?.value||'').trim();
    const body = { user_id: userId, course_id: courseId, final_grade: vf.val };
    if (moduleId) body.module_id = moduleId;
    const r = await apiRequest('POST', '/api/teacher/grades', body);
    if (!r.ok) { showCrudMessage('error','Error al crear la nota'); console.error(r); return; }
    showCrudMessage('success','Nota creada');
    await loadGrades();
  }

  async function updateGrade(){
    const idEl = document.getElementById('grade-crud-id');
    const courseSel = document.getElementById('grade-course-select');
    const userIdEl = document.getElementById('grade-crud-user');
    const moduleEl = document.getElementById('grade-crud-module');
    const finalEl = document.getElementById('grade-crud-final');
    const id = parseInt(idEl?.value||'0',10);
    if (!id) { showCrudMessage('error','ID de nota requerido'); return; }
    const body = {};
    const courseId = courseSel?.value||'';
    const userId = (userIdEl?.value||'').trim();
    const moduleId = (moduleEl?.value||'').trim();
    const finalVal = finalEl?.value;
    if (courseId) body.course_id = courseId;
    if (userId) body.user_id = userId;
    if (moduleId) body.module_id = moduleId;
    if (finalVal !== '' && finalVal != null) {
      const vf = validateFinalGrade(finalVal);
      if (!vf.ok) { showCrudMessage('error', vf.msg); return; }
      body.final_grade = vf.val;
    }
    const r = await apiRequest('PATCH', `/api/teacher/grades?id=${encodeURIComponent(id)}`, body);
    if (!r.ok) { showCrudMessage('error','Error al actualizar la nota'); console.error(r); return; }
    showCrudMessage('success','Nota actualizada');
    await loadGrades();
  }

  async function deleteGrade(){
    const idEl = document.getElementById('grade-crud-id');
    const id = parseInt(idEl?.value||'0',10);
    if (!id) { showCrudMessage('error','ID de nota requerido'); return; }
    const ok = window.confirm('¿Eliminar la nota seleccionada?');
    if (!ok) return;
    const r = await apiRequest('DELETE', `/api/teacher/grades?id=${encodeURIComponent(id)}`);
    if (!r.ok) { showCrudMessage('error','Error al eliminar la nota'); console.error(r); return; }
    showCrudMessage('success','Nota eliminada');
    await loadGrades();
  }

  document.addEventListener('DOMContentLoaded', () => {
    const courseSel = document.getElementById('grade-course-select');
    const exSel = document.getElementById('grade-exercise-select');
    const btn = document.getElementById('btn-load-grades');
    if (courseSel) {
      // Cargar cursos al iniciar y, si hay curso, cargar ejercicios y notas
      Promise.resolve().then(() => window.populateGradeCourses && window.populateGradeCourses()).then(async () => {
        const cid = courseSel.value || '';
        if (cid) { await populateGradeExercises(cid); await loadGrades(); }
      });
      courseSel.addEventListener('change', async () => {
        await populateGradeExercises(courseSel.value || '');
        await loadGrades();
      });
    }
    if (btn) { btn.addEventListener('click', loadGrades); }
    if (exSel) { exSel.addEventListener('change', loadGrades); }

    // CRUD
    const btnCreate = document.getElementById('btn-grade-create');
    const btnUpdate = document.getElementById('btn-grade-update');
    const btnDelete = document.getElementById('btn-grade-delete');
    if (btnCreate) btnCreate.addEventListener('click', createGrade);
    if (btnUpdate) btnUpdate.addEventListener('click', updateGrade);
    if (btnDelete) btnDelete.addEventListener('click', deleteGrade);
  });

  // Exponer helpers
  window.populateGradeCourses = populateGradeCourses;
  window.populateGradeExercises = populateGradeExercises;
  window.loadGrades = loadGrades;
  window.createGrade = createGrade;
  window.updateGrade = updateGrade;
  window.deleteGrade = deleteGrade;
})();
(function(){
  // Control de Laboratorio: cargar computadoras y ejecutar acciones
  const labSel = document.getElementById('teacher-lab-select');
  const compContainer = document.getElementById('lab-computers-container');
  const statusEl = document.getElementById('lab-status-container');

  function setStatus(text){ if (statusEl) statusEl.textContent = text || ''; }

  function renderLabComputers(computers){
    if (!compContainer) return;
    const list = Array.isArray(computers) ? computers : [];
    if (list.length === 0){
      compContainer.innerHTML = '<div class="empty">Sin computadoras para este laboratorio</div>';
      setStatus('Sin computadoras');
      return;
    }
    const counts = { online:0, offline:0, locked:0 };
    compContainer.innerHTML = list.map(c => {
      const st = (c.status || '').toLowerCase();
      if (st === 'online') counts.online++; else if (st === 'locked') counts.locked++; else counts.offline++;
      const last = c.last_seen ? new Date(c.last_seen).toLocaleString() : '';
      return `
        <div class="computer-card" data-id="${c.id}" data-status="${st}">
          <label class="select">
            <input type="checkbox" class="comp-check" data-id="${c.id}"> Seleccionar
          </label>
          <div class="title">${c.name || ('PC #' + c.id)}</div>
          <div class="meta">IP: ${c.ip_address || '-'} · MAC: ${c.mac_address || '-'}</div>
          <div class="status">Estado: ${st || '-'}</div>
          <div class="last">Última vez: ${last}</div>
        </div>`;
    }).join('');
    setStatus(`Online: ${counts.online} · Bloqueadas: ${counts.locked} · Offline: ${counts.offline}`);
  }

  async function loadLabComputers(){
    if (!compContainer) return;
    const labId = parseInt(labSel?.value || '0', 10) || 0;
    compContainer.innerHTML = '<div class="loading">Cargando computadoras…</div>';
    try {
      const url = labId > 0 ? (`/api/teacher/computers?lab_id=${encodeURIComponent(labId)}`) : '/api/teacher/computers';
      const res = await fetch(url, { headers: { 'Accept':'application/json' }});
      const j = await res.json();
      renderLabComputers(j.data || []);
    } catch(e){
      console.error('loadLabComputers error', e);
      compContainer.innerHTML = '<div class="error">Error al cargar computadoras</div>';
      setStatus('Error de carga');
    }
  }

  function getSelectedComputerIds(){
    return Array.from(document.querySelectorAll('.comp-check:checked'))
      .map(el => parseInt(el.getAttribute('data-id')||'0',10))
      .filter(n => n>0);
  }

  async function sendComputerAction(actionType, ids){
    try {
      const headers = { 'Content-Type':'application/json', 'Accept':'application/json' };
      if (window.CSRF_TOKEN) headers['X-CSRF-Token'] = window.CSRF_TOKEN;
      const res = await fetch('/api/teacher/actions/computers', {
        method: 'POST',
        headers,
        body: JSON.stringify({ action_type: actionType, computer_ids: ids })
      });
      const j = await res.json().catch(()=>({}));
      if (!res.ok){ throw new Error(j.error || ('HTTP '+res.status)); }
      return j;
    } catch(e){
      throw e;
    }
  }

  async function performGroupAction(actionType){
    const ids = getSelectedComputerIds();
    if (ids.length === 0){ alert('Selecciona computadoras primero'); return; }
    try {
      setStatus('Ejecutando acción…');
      const r = await sendComputerAction(actionType, ids);
      alert(`Acción aplicada: ${actionType}. Registradas: ${r.created ?? ids.length}`);
      await loadLabComputers();
    } catch(e){
      console.error('performGroupAction error', e);
      alert('No se pudo ejecutar la acción');
      setStatus('Error al ejecutar acción');
    }
  }

  async function startLabSession(){
    // Encender computadoras seleccionadas; si no hay selección, usar todas las listadas
    let ids = getSelectedComputerIds();
    if (ids.length === 0){
      ids = Array.from(document.querySelectorAll('.comp-check')).map(el => parseInt(el.getAttribute('data-id')||'0',10)).filter(n=>n>0);
    }
    if (ids.length === 0){ alert('No hay computadoras para iniciar la sesión'); return; }
    try {
      setStatus('Iniciando sesión de laboratorio…');
      const r = await sendComputerAction('power_on', ids);
      alert(`Sesión iniciada. Computadoras encendidas: ${r.created ?? ids.length}`);
      await loadLabComputers();
    } catch(e){
      console.error('startLabSession error', e);
      alert('No se pudo iniciar la sesión');
      setStatus('Error al iniciar sesión');
    }
  }

  // Exponer globales para los manejadores declarados en la vista
  window.loadLabComputers = loadLabComputers;
  window.performGroupAction = performGroupAction;
  window.startLabSession = startLabSession;

  document.addEventListener('DOMContentLoaded', () => {
    // Si estamos en la sección de laboratorio y hay un select, cargar inicialmente
    if (document.getElementById('lab-control') && labSel){ loadLabComputers(); }
  });
})();