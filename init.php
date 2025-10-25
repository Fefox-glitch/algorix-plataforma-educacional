<?php
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax'
  ]);
  session_start();
}

// Session timeout y regeneración
$timeout = 1800; // 30 minutos
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
    session_unset();
    session_destroy();
    if (!headers_sent()) { session_start(); }
}
$_SESSION['LAST_ACTIVITY'] = time();
if (!isset($_SESSION['REGENERATE_AT']) || time() > $_SESSION['REGENERATE_AT']) {
    if (!headers_sent()) {
        session_regenerate_id(true);
    }
    $_SESSION['REGENERATE_AT'] = time() + 600; // cada 10 minutos
}

// Encabezados de seguridad
if (!headers_sent()) {
  if ($secure) {
      header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
  }
  header('X-Frame-Options: SAMEORIGIN');
  header('X-Content-Type-Options: nosniff');
  header('Referrer-Policy: no-referrer-when-downgrade');
  header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
  header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https:; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; connect-src 'self' https:; frame-ancestors 'self';");
}

// Configuración de rutas y URLs
// Leer BASE_URL desde .env si está disponible
$envBase = isset($_ENV['BASE_URL']) ? rtrim($_ENV['BASE_URL'], '/') : '';
define('BASE_URL', $envBase);  // fallback '' para entorno local con router
define('APP_ROOT', dirname(__FILE__));
define('PUBLIC_PATH', '/public');

// Incluir archivos de configuración y utilidades
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/src/Utils/Render.php';

function base_url($path = '') {
    return BASE_URL . ($path ? '/' . ltrim($path, '/') : '');
}

// Verificar si el usuario está autenticado
function isAuthenticated() {
    return isset($_SESSION['user']) && isset($_SESSION['user']['id']);
}

// Verificar rol del usuario
function hasRole($roles) {
    if (!isAuthenticated()) {
        return false;
    }
    $userRole = $_SESSION['user']['role'] ?? '';
    return in_array($userRole, (array)$roles);
}

// Rate limiting simple por IP
function rate_limit($key, $max = 30, $windowSeconds = 60) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $k = $ip . '|' . $key;
    $now = time();
    if (!isset($_SESSION['rate'][$k])) {
        $_SESSION['rate'][$k] = ['count' => 0, 'start' => $now];
    }
    $bucket = &$_SESSION['rate'][$k];
    if (($now - $bucket['start']) > $windowSeconds) {
        $bucket['count'] = 0;
        $bucket['start'] = $now;
    }
    $bucket['count']++;
    return $bucket['count'] <= $max;
}

// Función para manejar las rutas
function handle_route($uri) {
    // Quitar /algorix/ del inicio
    $uri = str_replace('/algorix/', '', $uri);
    $uri = trim($uri, '/');

    // Si no hay URI, mostrar la página principal
    if (empty($uri)) {
        return 'home';
    }

    // Manejar rutas específicas
    $parts = explode('/', $uri);
    $base = $parts[0];

    switch ($base) {
        case 'auth':
            $action = $parts[1] ?? 'login';
            return 'auth/' . $action;

        case 'student':
            // Verificar autenticación y rol
            if (!hasRole('student')) {
                redirect('auth/login');
            }
            return $uri;

        case 'teacher':
            if (!hasRole('teacher')) {
                redirect('auth/login');
            }
            return $uri;

        case 'admin':
            if (!hasRole('admin')) {
                redirect('auth/login');
            }
            return $uri;
    }

    return $uri;
}

function assets_url($path = '') {
    return base_url('assets/' . ltrim($path, '/'));
}

function styles_url($path = '') {
    return base_url('styles/' . ltrim($path, '/'));
}

function script_url($path = '') {
    return base_url(ltrim($path, '/'));
}

function view_path($view) {
    return __DIR__ . '/views/' . ltrim($view, '/') . '.php';
}

// Redirección
function redirect($path = '') {
    header('Location: ' . base_url($path));
    exit;
}

function checkCsrfToken(): void {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method === 'GET') { return; }
    $token = $_POST['csrf_token'] ?? ($_GET['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
    if (class_exists('App\\Core\\Security')) {
        if (!\App\Core\Security::verifyCsrfToken($token)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'invalid_csrf'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    } else {
        if ($token === '' || ($token !== ($_SESSION['csrf_token'] ?? ''))) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'invalid_csrf'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}

$__req_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($__req_method !== 'GET') {
    checkCsrfToken();
}

