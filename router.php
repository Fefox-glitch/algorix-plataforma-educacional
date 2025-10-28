<?php

// Router para el servidor embebido de PHP
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__ . $uri;

// Si es un archivo estático existente, dejar que el server lo sirva
if ($uri !== '/' && file_exists($file) && is_file($file)) {
    return false;
}

// En caso contrario, delegar a index.php
require __DIR__ . '/index.php';
