<?php
// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Supabase configuration (soporta nombres de variables .env alternativos)
define('SUPABASE_URL', $_ENV['SUPABASE_URL'] ?? ($_ENV['VITE_SUPABASE_URL'] ?? ''));
define('SUPABASE_KEY', $_ENV['SUPABASE_KEY'] ?? ($_ENV['VITE_SUPABASE_ANON_KEY'] ?? ''));
// Nuevo: clave de service role para operaciones privilegiadas en el servidor
define('SUPABASE_SERVICE_ROLE_KEY', $_ENV['SUPABASE_SERVICE_ROLE_KEY'] ?? ($_ENV['VITE_SUPABASE_SERVICE_ROLE_KEY'] ?? ''));

// Application configuration
define('APP_NAME', 'Algorix');
define('APP_URL', 'http://localhost/algorix');
define('APP_VERSION', '1.0.0');

// Session configuration
// Estas configuraciones deben establecerse antes de iniciar la sesión
// Se moverán al inicio del script para evitar advertencias
$sessionConfig = [
    'cookie_httponly' => 1,
    'use_only_cookies' => 1,
    'cookie_secure' => 0 // Set to 1 in production with HTTPS
];

// Error reporting in development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fallback offline simple para endpoints usados por Auth (usuarios)
function offlineSupabaseRequest($method, $endpoint, $data = null, $filters = []) {
    // Almacenamiento local en JSON
    $dir = __DIR__ . '/storage';
    $path = $dir . '/users.json';
    if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
    if (!file_exists($path)) { file_put_contents($path, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); }

    $raw = file_get_contents($path);
    $users = json_decode($raw, true);
    if (!is_array($users)) { $users = []; }

    // Parsear endpoint y query
    $parts = explode('?', $endpoint, 2);
    $table = $parts[0];
    $query = $parts[1] ?? '';

    if ($table !== 'users') {
        // Endpoints no soportados en modo offline
        return ['code' => 200, 'data' => [], 'error' => null];
    }

    if ($method === 'GET') {
        $email = null;
        if (!empty($query)) {
            // Esperamos formato email=eq.<valor>
            parse_str($query, $qs);
            if (isset($qs['email'])) {
                $v = $qs['email'];
                $email = (strpos($v, 'eq.') === 0) ? substr($v, 3) : $v;
                $email = urldecode($email);
            }
        }
        if ($email === null) {
            return ['code' => 200, 'data' => $users, 'error' => null];
        }
        $res = array_values(array_filter($users, function($u) use ($email) {
            return isset($u['email']) && strtolower($u['email']) === strtolower($email);
        }));
        return ['code' => 200, 'data' => $res, 'error' => null];
    }

    if ($method === 'POST') {
        // Insertar usuario
        $new = $data ?? [];
        if (!isset($new['email'])) {
            return ['code' => 400, 'data' => null, 'error' => 'Email requerido'];
        }
        foreach ($users as $u) {
            if (strtolower($u['email']) === strtolower($new['email'])) {
                return ['code' => 409, 'data' => null, 'error' => 'Usuario ya existe'];
            }
        }
        if (!isset($new['id']) || !$new['id']) {
            // ID simple offline
            $new['id'] = 'offline-' . bin2hex(random_bytes(6));
        }
        $users[] = $new;
        file_put_contents($path, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return ['code' => 201, 'data' => [$new], 'error' => null];
    }

    return ['code' => 200, 'data' => [], 'error' => null];
}

// Helper para leer bandera de OFFLINE desde .env o storage/ai_config.json
function isOfflineModeEnabled() {
    $envFlag = (isset($_ENV['OFFLINE_MODE']) && $_ENV['OFFLINE_MODE'] === 'true');
    if ($envFlag) { return true; }
    $cfgPath = __DIR__ . '/storage/ai_config.json';
    if (file_exists($cfgPath)) {
        $raw = @file_get_contents($cfgPath);
        $cfg = @json_decode($raw, true);
        if (is_array($cfg) && isset($cfg['OFFLINE_MODE'])) {
            return ($cfg['OFFLINE_MODE'] === true || $cfg['OFFLINE_MODE'] === 'true');
        }
    }
    return false;
}

// Supabase API helper function (uses anon/service key)
function supabaseRequest($method, $endpoint, $data = null, $filters = []) {
    // Fallback offline para usuarios si OFFLINE está habilitado
    $tablePart = explode('?', $endpoint, 2)[0];
    if (isOfflineModeEnabled() && $tablePart === 'users') {
        return offlineSupabaseRequest($method, $endpoint, $data, $filters);
    }

    // Validar configuración antes de hacer la petición real a Supabase
    if (empty(SUPABASE_URL) || empty(SUPABASE_KEY)) {
        return [
            'code' => 503,
            'data' => null,
            'error' => 'Supabase no está configurado (faltan SUPABASE_URL o SUPABASE_KEY)'
        ];
    }

    $url = SUPABASE_URL . '/rest/v1/' . $endpoint;

    // Agregar filtros a la URL si existen
    if (!empty($filters)) {
        $queryString = http_build_query($filters);
        $url .= (strpos($url, '?') === false ? '?' : '&') . $queryString;
    }

    // Determinar perfil de esquema a partir del endpoint si usa prefijo (ej: algorix.labs)
    $schema = null;
    $tablePart = explode('?', $endpoint, 2)[0];
    if (strpos($tablePart, '.') !== false) {
        $schema = explode('.', $tablePart, 2)[0];
    }
    $isWrite = in_array(strtoupper($method), ['POST', 'PUT', 'PATCH', 'DELETE'], true);
    // Ajuste: usar service role también para lecturas de tablas con RLS de "authenticated"
    $requireAuthTables = ['computer_labs','computers','student_groups','group_members','computer_assignments','lab_sessions','courses','modules','exercises','submissions','grades'];
    $usesServiceForRead = (strtoupper($method) === 'GET' && in_array($tablePart, $requireAuthTables, true) && !empty(SUPABASE_SERVICE_ROLE_KEY));
    $keyToUse = (($isWrite || $usesServiceForRead) && !empty(SUPABASE_SERVICE_ROLE_KEY)) ? SUPABASE_SERVICE_ROLE_KEY : SUPABASE_KEY;

    $headers = [
        'apikey: ' . $keyToUse,
        'Authorization: Bearer ' . $keyToUse,
        'Content-Type: application/json',
        'Accept: application/json',
        'Prefer: ' . ($isWrite ? 'return=representation' : 'return=representation')
    ];
    if ($schema) {
        if ($isWrite) { $headers[] = 'Content-Profile: ' . $schema; }
        else { $headers[] = 'Accept-Profile: ' . $schema; }
    }

    // Camino normal con cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    $responseData = json_decode($response, true);

    // Log errors para debugging
    if ($httpCode >= 400 || !empty($error)) {
        error_log("Supabase request error: $method $endpoint - HTTP $httpCode - Error: $error - Response: $response");
    }

    return [
        'code' => $httpCode,
        'data' => $responseData,
        'error' => $error
    ];
}

// Get database connection (for compatibility - returns Supabase info)
function getDBConnection() {
    return [
        'type' => 'supabase',
        'url' => SUPABASE_URL,
        'connected' => !empty(SUPABASE_URL) && !empty(SUPABASE_KEY)
    ];
}