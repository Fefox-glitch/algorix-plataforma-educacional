<?php
/**
 * Autoloader para cargar clases automáticamente siguiendo PSR-4
 * 
 * Este autoloader es compatible con PSR-4 y maneja correctamente los namespaces
 * para una mejor organización del código.
 */

// Verificar si Composer está disponible
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    // Si existe composer, usarlo como primera opción
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    // Autoloader personalizado como respaldo
    spl_autoload_register(function ($className) {
        // Prefijo base para nuestro namespace
        $prefix = 'App\\';
        $baseDir = __DIR__ . '/src/';

        // Si la clase usa nuestro namespace, intentar PSR-4
        $len = strlen($prefix);
        if (strncmp($prefix, $className, $len) === 0) {
            $relativeClass = substr($className, $len);
            $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
        }

        // Fallback: buscar clases sin namespace (estructura antigua)
        $paths = [
            __DIR__ . '/src/',
            __DIR__ . '/src/Core/',
            __DIR__ . '/src/Controllers/',
            __DIR__ . '/src/Models/',
            __DIR__ . '/src/Services/',
            __DIR__ . '/src/Middleware/',
            __DIR__ . '/src/Utils/'
        ];

        foreach ($paths as $path) {
            $classFile = $path . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
            if (file_exists($classFile)) {
                require_once $classFile;
                return true;
            }
        }
    });
}

// Cargar archivos de funciones y utilidades
if (file_exists(__DIR__ . '/src/Utils/functions.php')) {
    require_once __DIR__ . '/src/Utils/functions.php';
}

if (file_exists(__DIR__ . '/src/Core/router.php')) {
    require_once __DIR__ . '/src/Core/router.php';
}