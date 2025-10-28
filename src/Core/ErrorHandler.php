<?php

namespace App\Core;

/**
 * Manejador centralizado de errores y excepciones
 */
class ErrorHandler
{
    private static $logPath;
    private static $isProduction;

    /**
     * Inicializa el manejador de errores
     */
    public static function initialize()
    {
        self::$logPath = __DIR__ . '/../../logs';
        self::$isProduction = getenv('APP_ENV') === 'production';

        // Crear directorio de logs si no existe
        if (!is_dir(self::$logPath)) {
            mkdir(self::$logPath, 0755, true);
        }

        // Configurar manejadores
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleFatalError']);

        // Configurar nivel de errores según entorno
        if (self::$isProduction) {
            error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
            ini_set('display_errors', 0);
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        }
    }

    /**
     * Maneja errores de PHP
     */
    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        $logMessage = date('Y-m-d H:i:s') . " - ERROR [$errno] $errstr in $errfile on line $errline\n";
        self::writeToLog($logMessage);

        if (self::$isProduction) {
            // En producción, mostrar mensaje genérico
            if (error_reporting() & $errno) {
                include __DIR__ . '/../../views/errors/error.php';
                exit(1);
            }
        } else {
            // En desarrollo, mostrar detalles
            echo "<div style='background:#f8d7da;color:#721c24;"
                . "padding:10px;margin:10px;border:1px solid #f5c6cb;"
                . "border-radius:4px'>";
            echo "<h3>Error detectado:</h3>";
            echo "<p><strong>Tipo:</strong> $errno</p>";
            echo "<p><strong>Mensaje:</strong> $errstr</p>";
            echo "<p><strong>Archivo:</strong> $errfile</p>";
            echo "<p><strong>Línea:</strong> $errline</p>";
            echo "</div>";
        }

        // No ejecutar el manejador de errores interno de PHP
        return true;
    }

    /**
     * Maneja excepciones no capturadas
     */
    public static function handleException($exception)
    {
        $logMessage = date('Y-m-d H:i:s') . " - EXCEPTION: " . $exception->getMessage() .
                     " in " . $exception->getFile() . " on line " . $exception->getLine() .
                     "\nStack trace: " . $exception->getTraceAsString() . "\n";
        self::writeToLog($logMessage);

        if (self::$isProduction) {
            // En producción, mostrar mensaje genérico
            include __DIR__ . '/../../views/errors/error.php';
        } else {
            // En desarrollo, mostrar detalles
            echo "<div style='background:#f8d7da;color:#721c24;"
                . "padding:10px;margin:10px;border:1px solid #f5c6cb;"
                . "border-radius:4px'>";
            echo "<h3>Excepción no capturada:</h3>";
            echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
            echo "<p><strong>Archivo:</strong> " . htmlspecialchars($exception->getFile()) . "</p>";
            echo "<p><strong>Línea:</strong> " . $exception->getLine() . "</p>";
            echo "<h4>Stack Trace:</h4>";
            echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
            echo "</div>";
        }

        exit(1);
    }

    /**
     * Maneja errores fatales
     */
    public static function handleFatalError()
    {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::handleError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    /**
     * Escribe en el archivo de log
     */
    private static function writeToLog($message)
    {
        $logFile = self::$logPath . '/error_' . date('Y-m-d') . '.log';
        file_put_contents($logFile, $message, FILE_APPEND);
    }
}
