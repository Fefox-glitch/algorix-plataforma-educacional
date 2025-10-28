<?php

namespace App\Core;

/**
 * Clase para gestionar la seguridad de la aplicación
 */
class Security
{
    /**
     * Sanitiza datos de entrada
     *
     * @param mixed $data Datos a sanitizar
     * @param string $type Tipo de sanitización (text, email, int, float, url)
     * @return mixed Datos sanitizados
     */
    public static function sanitize($data, $type = 'text')
    {
        if (is_array($data)) {
            return array_map(function ($item) use ($type) {
                return self::sanitize($item, $type);
            }, $data);
        }

        switch ($type) {
            case 'email':
                return filter_var($data, FILTER_SANITIZE_EMAIL);
            case 'int':
                return filter_var($data, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case 'url':
                return filter_var($data, FILTER_SANITIZE_URL);
            case 'text':
            default:
                return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
        }
    }

    /**
     * Valida datos según reglas específicas
     *
     * @param mixed $data Datos a validar
     * @param string $type Tipo de validación (email, int, float, url, etc.)
     * @param array $options Opciones adicionales de validación
     * @return bool True si los datos son válidos
     */
    public static function validate($data, $type, $options = [])
    {
        switch ($type) {
            case 'required':
                return !empty($data);
            case 'email':
                return filter_var($data, FILTER_VALIDATE_EMAIL) !== false;
            case 'int':
                return filter_var($data, FILTER_VALIDATE_INT) !== false;
            case 'float':
                return filter_var($data, FILTER_VALIDATE_FLOAT) !== false;
            case 'url':
                return filter_var($data, FILTER_VALIDATE_URL) !== false;
            case 'length':
                $length = strlen($data);
                $min = $options['min'] ?? 0;
                $max = $options['max'] ?? PHP_INT_MAX;
                return $length >= $min && $length <= $max;
            case 'regex':
                return isset($options['pattern']) && preg_match($options['pattern'], $data);
            default:
                return true;
        }
    }

    /**
     * Genera un token CSRF
     *
     * @return string Token CSRF
     */
    public static function generateCsrfToken()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verifica un token CSRF
     *
     * @param string $token Token a verificar
     * @return bool True si el token es válido
     */
    public static function verifyCsrfToken($token)
    {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Verifica si el usuario tiene el rol requerido
     *
     * @param string|array $requiredRoles Rol o roles requeridos
     * @return bool True si el usuario tiene el rol requerido
     */
    public static function checkRole($requiredRoles)
    {
        if (empty($_SESSION['user']) || empty($_SESSION['user']['role'])) {
            return false;
        }

        $userRole = $_SESSION['user']['role'];

        if (is_array($requiredRoles)) {
            return in_array($userRole, $requiredRoles);
        }

        return $userRole === $requiredRoles;
    }

    /**
     * Verifica si el usuario está autenticado
     *
     * @return bool True si el usuario está autenticado
     */
    public static function isAuthenticated()
    {
        return !empty($_SESSION['user']) && !empty($_SESSION['user']['id']);
    }

    /**
     * Genera un hash seguro para contraseñas
     *
     * @param string $password Contraseña a hashear
     * @return string Hash de la contraseña
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verifica una contraseña contra su hash
     *
     * @param string $password Contraseña a verificar
     * @param string $hash Hash almacenado
     * @return bool True si la contraseña es correcta
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
}
