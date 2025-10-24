<?php
require_once __DIR__ . '/../../init.php';

// Simulación de usuario admin para preview
$user = [
  'id' => 1,
  'name' => 'Admin Demo',
  'role' => 'admin'
];
// Establecer sesión simulada para permitir llamadas a /api/admin/* en preview
$_SESSION['user'] = $user;

$pageTitle = 'Preview Admin';
$userRole = 'admin';
$content = renderView('admin/dashboard', ['user' => $user]);

include __DIR__ . '/../../views/shared/layout.php';