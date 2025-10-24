<?php
namespace App\Core;

/**
 * Sistema de logs para la aplicación
 */
class Logger {
    private static $instance;
    private $logPath;
    private $logLevel;
    
    // Niveles de log
    const DEBUG = 100;
    const INFO = 200;
    const NOTICE = 250;
    const WARNING = 300;
    const ERROR = 400;
    const CRITICAL = 500;
    const ALERT = 550;
    const EMERGENCY = 600;
    
    private $levelNames = [
        self::DEBUG => 'DEBUG',
        self::INFO => 'INFO',
        self::NOTICE => 'NOTICE',
        self::WARNING => 'WARNING',
        self::ERROR => 'ERROR',
        self::CRITICAL => 'CRITICAL',
        self::ALERT => 'ALERT',
        self::EMERGENCY => 'EMERGENCY'
    ];
    
    /**
     * Constructor privado (patrón Singleton)
     */
    private function __construct() {
        $this->logPath = __DIR__ . '/../../logs';
        $this->logLevel = getenv('APP_ENV') === 'production' ? self::ERROR : self::DEBUG;
        
        // Crear directorio de logs si no existe
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }
    
    /**
     * Obtiene la instancia única del logger
     * 
     * @return Logger
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Establece el nivel mínimo de log
     * 
     * @param int $level Nivel de log
     * @return void
     */
    public function setLogLevel($level) {
        $this->logLevel = $level;
    }
    
    /**
     * Registra un mensaje de log
     * 
     * @param string $message Mensaje a registrar
     * @param int $level Nivel de log
     * @param array $context Contexto adicional
     * @return void
     */
    public function log($message, $level = self::INFO, array $context = []) {
        if ($level < $this->logLevel) {
            return;
        }
        
        $levelName = $this->levelNames[$level] ?? 'UNKNOWN';
        $timestamp = date('Y-m-d H:i:s');
        $logFile = $this->logPath . '/app_' . date('Y-m-d') . '.log';
        
        // Formatear mensaje
        $logMessage = "[$timestamp] [$levelName] $message";
        
        // Añadir contexto si existe
        if (!empty($context)) {
            $logMessage .= ' ' . json_encode($context);
        }
        
        $logMessage .= PHP_EOL;
        
        // Escribir en el archivo de log
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
    
    /**
     * Registra un mensaje de debug
     * 
     * @param string $message Mensaje a registrar
     * @param array $context Contexto adicional
     * @return void
     */
    public function debug($message, array $context = []) {
        $this->log($message, self::DEBUG, $context);
    }
    
    /**
     * Registra un mensaje informativo
     * 
     * @param string $message Mensaje a registrar
     * @param array $context Contexto adicional
     * @return void
     */
    public function info($message, array $context = []) {
        $this->log($message, self::INFO, $context);
    }
    
    /**
     * Registra un mensaje de advertencia
     * 
     * @param string $message Mensaje a registrar
     * @param array $context Contexto adicional
     * @return void
     */
    public function warning($message, array $context = []) {
        $this->log($message, self::WARNING, $context);
    }
    
    /**
     * Registra un mensaje de error
     * 
     * @param string $message Mensaje a registrar
     * @param array $context Contexto adicional
     * @return void
     */
    public function error($message, array $context = []) {
        $this->log($message, self::ERROR, $context);
    }
    
    /**
     * Registra un mensaje crítico
     * 
     * @param string $message Mensaje a registrar
     * @param array $context Contexto adicional
     * @return void
     */
    public function critical($message, array $context = []) {
        $this->log($message, self::CRITICAL, $context);
    }
}