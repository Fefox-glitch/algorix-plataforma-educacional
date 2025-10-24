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

// 2) Insertar labs
$labsToCreate = [
  ['name' => 'Laboratorio A', 'location' => 'Edificio Norte, Piso 1', 'capacity' => 24, 'is_active' => true],
  ['name' => 'Laboratorio B', 'location' => 'Edificio Sur, Piso 2', 'capacity' => 16, 'is_active' => true]
];
$labsRes = supabaseRequest('POST', 'computer_labs', $labsToCreate);
if ($labsRes['code'] !== 201 || empty($labsRes['data'])) {
  out('[ERROR] No se pudieron crear labs');
  out('HTTP ' . $labsRes['code'] . ' ' . ($labsRes['error'] ?: json_encode($labsRes['data'])));
  exit(1);
}
$labs = $labsRes['data'];
foreach ($labs as $lab) { out('[LAB] ' . $lab['id'] . ' ' . $lab['name']); }

$labA = $labs[0]['id'];
$labB = $labs[1]['id'];

// 3) Insertar computers
$computersToCreate = [
  ['name'=>'PC-A-01','lab_id'=>$labA,'ip_address'=>'10.0.1.101','mac_address'=>null,'status'=>'online'],
  ['name'=>'PC-A-02','lab_id'=>$labA,'ip_address'=>'10.0.1.102','mac_address'=>null,'status'=>'offline'],
  ['name'=>'PC-A-03','lab_id'=>$labA,'ip_address'=>'10.0.1.103','mac_address'=>null,'status'=>'maintenance'],
  ['name'=>'PC-B-01','lab_id'=>$labB,'ip_address'=>'10.0.2.101','mac_address'=>null,'status'=>'online'],
  ['name'=>'PC-B-02','lab_id'=>$labB,'ip_address'=>'10.0.2.102','mac_address'=>null,'status'=>'offline']
];
$computersRes = supabaseRequest('POST', 'computers', $computersToCreate);
if ($computersRes['code'] !== 201 || empty($computersRes['data'])) {
  out('[ERROR] No se pudieron crear computadoras');
  out('HTTP ' . $computersRes['code'] . ' ' . ($computersRes['error'] ?: json_encode($computersRes['data'])));
  exit(1);
}
foreach ($computersRes['data'] as $c) { out('[PC] ' . $c['id'] . ' ' . $c['name'] . ' (' . $c['status'] . ')'); }

// 4) Insertar grupos
$groupsToCreate = [
  ['name'=>'Grupo 1','teacher_id'=>$teacherId,'lab_id'=>$labA,'is_active'=>true],
  ['name'=>'Grupo 2','teacher_id'=>$teacherId,'lab_id'=>$labB,'is_active'=>true]
];
$groupsRes = supabaseRequest('POST', 'student_groups', $groupsToCreate);
if ($groupsRes['code'] !== 201 || empty($groupsRes['data'])) {
  out('[ERROR] No se pudieron crear grupos');
  out('HTTP ' . $groupsRes['code'] . ' ' . ($groupsRes['error'] ?: json_encode($groupsRes['data'])));
  exit(1);
}
foreach ($groupsRes['data'] as $g) { out('[GRUPO] ' . $g['id'] . ' ' . $g['name']); }

out("\nSeed completado.");