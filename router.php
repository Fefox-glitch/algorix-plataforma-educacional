<?php

// Router para el servidor embebido de PHP
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Document root esperado cuando se invoca con: php -S localhost:8000 -t public router.php
$docRoot = __DIR__ . '/public';
$staticFile = $docRoot . $uri;

// Si es un archivo estático existente bajo public/, dejar que el server lo sirva
if ($uri !== '/' && file_exists($staticFile) && is_file($staticFile)) {
    return false;
}

// En caso contrario, delegar a index.php
require __DIR__ . '/index.php';
