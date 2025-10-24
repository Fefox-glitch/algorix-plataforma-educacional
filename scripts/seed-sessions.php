<?php
require_once __DIR__ . '/../config.php';

function out($msg){ echo $msg, "\n"; }

// 1) Buscar teacher de prueba
$teacherEmail = 'teacher1@example.com';
out("Buscando teacher: $teacherEmail");
$teacherRes = supabaseRequest('GET', 'users?select=id,email,role&email=eq.' . urlencode($teacherEmail) . '&role=eq.teacher');
if ($teacherRes['code'] >= 400 || empty($teacherRes['data'])) {
  out('[ERROR] No se pudo obtener el teacher (¿seed-users ejecutado?).');
  out('HTTP ' . $teacherRes['code'] . ' ' . ($teacherRes['error'] ?: json_encode($teacherRes['data'])));
  exit(1);
}
$teacherId = $teacherRes['data'][0]['id'];
out('Teacher id: ' . $teacherId);

// 2) Obtener labs por nombre
$labsRes = supabaseRequest('GET', 'computer_labs?select=id,name&limit=100');
if ($labsRes['code'] >= 400) {
  out('[ERROR] No se pudieron obtener labs (error de lectura).');
  out('HTTP ' . $labsRes['code'] . ' ' . ($labsRes['error'] ?: json_encode($labsRes['data'])));
  exit(1);
}
$labsByName = [];
if (!empty($labsRes['data'])) {
  foreach ($labsRes['data'] as $lab) { $labsByName[$lab['name']] = $lab['id']; }
}
if (empty($labsByName)) {
  out('[WARN] Lectura de labs vacía; usando IDs conocidos del último seed: 7 y 8');
  $labsByName['Laboratorio A'] = 7;
  $labsByName['Laboratorio B'] = 8;
}
if (!isset($labsByName['Laboratorio A']) || !isset($labsByName['Laboratorio B'])) {
  out('[ERROR] Faltan labs esperados: Laboratorio A/B');
  exit(1);
}

// 3) Obtener grupos por nombre
$groupsRes = supabaseRequest('GET', 'student_groups?select=id,name,teacher_id&limit=100');
if ($groupsRes['code'] >= 400) {
  out('[ERROR] No se pudieron obtener grupos (error de lectura).');
  out('HTTP ' . $groupsRes['code'] . ' ' . ($groupsRes['error'] ?: json_encode($groupsRes['data'])));
  exit(1);
}
$groupIds = [];
if (!empty($groupsRes['data'])) {
  foreach ($groupsRes['data'] as $g) {
    if (in_array($g['name'], ['Grupo 1','Grupo 2'], true)) {
      $groupIds[$g['name']] = $g['id'];
    }
  }
}
if (empty($groupIds)) {
  out('[WARN] Lectura de grupos vacía; usando IDs conocidos del último seed: Grupo 1=3, Grupo 2=4');
  $groupIds['Grupo 1'] = 3;
  $groupIds['Grupo 2'] = 4;
}
if (!isset($groupIds['Grupo 1']) || !isset($groupIds['Grupo 2'])) {
  out('[ERROR] Faltan grupos esperados: Grupo 1/Grupo 2');
  exit(1);
}

// 4) Preparar sesiones (dos activas y una finalizada)
$now = time();
$payload = [
  [
    'lab_id' => $labsByName['Laboratorio A'],
    'group_id' => $groupIds['Grupo 1'],
    'teacher_id' => $teacherId,
    'started_at' => date('c', $now - 45*60), // hace 45 min
    'ended_at' => null,
    'notes' => 'Clase de repaso'
  ],
  [
    'lab_id' => $labsByName['Laboratorio B'],
    'group_id' => $groupIds['Grupo 2'],
    'teacher_id' => $teacherId,
    'started_at' => date('c', $now - 10*60), // hace 10 min
    'ended_at' => null,
    'notes' => 'Práctica guiada'
  ],
  [
    'lab_id' => $labsByName['Laboratorio A'],
    'group_id' => $groupIds['Grupo 1'],
    'teacher_id' => $teacherId,
    'started_at' => date('c', $now - 120*60), // hace 2h
    'ended_at' => date('c', $now - 60*60),    // terminó hace 1h
    'notes' => 'Sesión anterior'
  ]
];

out('Insertando sesiones...');
$res = supabaseRequest('POST', 'lab_sessions', $payload);
if ($res['code'] !== 201 || empty($res['data'])) {
  out('[ERROR] No se pudieron crear sesiones');
  out('HTTP ' . $res['code'] . ' ' . ($res['error'] ?: json_encode($res['data'])));
  exit(1);
}

foreach ($res['data'] as $sess) {
  $sid = $sess['id'] ?? 'n/a';
  $lab = $sess['lab_id'] ?? 'n/a';
  $grp = $sess['group_id'] ?? 'n/a';
  $end = $sess['ended_at'] ?? null;
  out('[SESION] id=' . $sid . ' lab=' . $lab . ' group=' . $grp . ' estado=' . ($end ? 'finalizada' : 'activa'));
}

out('Seed de sesiones completado.');