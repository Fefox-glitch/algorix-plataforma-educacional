<?php
/**
 * Bootstrap de la aplicación
 */

// Cargar el autoloader (ir a la raíz)
require_once __DIR__ . '/../../autoload.php';

// Cargar configuración (archivo config en la raíz)
require_once __DIR__ . '/../../config.php';

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inicializar el manejador de errores
if (class_exists('\\App\\Core\\ErrorHandler')) {
    // El manejador de errores se inicializa automáticamente al final de su definición
    // No es necesario llamar a ErrorHandler::initialize() aquí
} else {
    // Configuración de errores tradicional como fallback
    if (getenv('APP_ENV') === 'production') {
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
        ini_set('display_errors', 0);
    } else {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    }
}

// Configurar zona horaria
date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'America/Santiago');

// Cargar funciones de utilidad desde la ruta correcta (raíz/src/Utils)
require_once __DIR__ . '/../../src/Utils/functions.php';

// Inicializar el sistema de logs
if (class_exists('\\App\\Core\\Logger')) {
    $logger = \App\Core\Logger::getInstance();
    $logger->info('Aplicación iniciada');
}

// Configurar cabeceras por defecto
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');