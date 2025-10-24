<?php
/**
 * Bootstrap de la aplicación
 */

// Cargar el autoloader
require_once __DIR__ . '/autoload.php';

// Cargar configuración
require_once __DIR__ . '/src/Config/config.php';

// Iniciar sesión
session_start();

// Configurar manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurar zona horaria
date_default_timezone_set('America/Santiago');

// Cargar funciones de utilidad
require_once __DIR__ . '/src/Utils/functions.php';

// Configurar cabeceras por defecto
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');