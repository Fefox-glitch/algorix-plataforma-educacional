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
    if (!headers_sent()) {
        session_start();
    }
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

// Incluir archivos de configuración y utilidades (composer autoload carga src/Utils/functions.php)
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/src/Utils/Render.php';

// Inicializar manejador global de errores
if (class_exists('App\\Core\\ErrorHandler')) {
    \App\Core\ErrorHandler::initialize();
}

// Aplicar verificación CSRF para métodos no-GET (usa helper definido en src/Utils/functions.php)
$__req_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($__req_method !== 'GET') {
    if (function_exists('checkCsrfToken')) {
        checkCsrfToken();
    }
}
