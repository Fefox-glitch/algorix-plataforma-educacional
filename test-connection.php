<?php
require_once __DIR__ . '/config.php';

echo "<h1>Test de Conexión a Supabase</h1>";

echo "<h2>Variables de Entorno</h2>";
echo "<p>SUPABASE_URL: " . (SUPABASE_URL ? "✓ Configurada" : "✗ No configurada") . "</p>";
echo "<p>SUPABASE_KEY: " . (SUPABASE_KEY ? "✓ Configurada" : "✗ No configurada") . "</p>";

echo "<h2>Test de Conexión</h2>";
$dbConn = getDBConnection();
echo "<pre>";
print_r($dbConn);
echo "</pre>";

echo "<h2>Test de Lectura de Usuarios</h2>";
$response = supabaseRequest('GET', 'users');
echo "<p>HTTP Code: " . $response['code'] . "</p>";
echo "<pre>";
print_r($response);
echo "</pre>";

echo "<h2>Test de Lectura de Cursos</h2>";
$response = supabaseRequest('GET', 'courses');
echo "<p>HTTP Code: " . $response['code'] . "</p>";
echo "<pre>";
print_r($response);
echo "</pre>";
