<?php
require_once __DIR__ . '/../init.php';

// Simulación de usuario estudiante para preview
$user = [
  'id' => 1,
  'name' => 'Estudiante Demo',
  'role' => 'student'
];

$pageTitle = 'Preview Estudiante';
$userRole = 'student';
$content = renderView('student/dashboard', ['user' => $user]);

include __DIR__ . '/../views/shared/layout.php';