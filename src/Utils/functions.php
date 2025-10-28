<?php

/**
 * Funciones de utilidad para la aplicación
 */

/**
 * Función auxiliar para el enrutamiento
 * Esta función ha sido movida a init.php para evitar duplicación
 */
// La función handle_route se ha eliminado de aquí para evitar la duplicación
// Ahora solo existe en init.php

/**
 * Renderiza una vista
 */
function render_view($view, $data = [])
{
    // Extraer los datos para que estén disponibles en la vista
    extract($data);
    // Construir la ruta al archivo de vista
    $viewPath = __DIR__ . '/../../views/' . $view . '.php';
    // Verificar si la vista existe
    if (file_exists($viewPath)) {
        // Iniciar el buffer de salida
        ob_start();
        // Incluir la vista
        include $viewPath;
        // Obtener el contenido del buffer y limpiarlo
        $content = ob_get_clean();
        return $content;
    } else {
        // Vista no encontrada
        return render_error(404, "Vista no encontrada: {$view}");
    }
}

/**
 * Renderiza un error
 */
function render_error($code, $message)
{
    http_response_code($code);
    return render_view('shared/error', [
        'code' => $code,
        'message' => $message
    ]);
}

/**
 * Función de redirección
 * Esta función ha sido movida a init.php para evitar duplicación
 */
// La función redirect se ha eliminado de aquí para evitar la duplicación
// Ahora solo existe en init.php

/**
 * Sanitiza la entrada del usuario
 */
function sanitize_input($input)
{

    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = sanitize_input($value);
        }
        return $input;
    }

    // Escapa etiquetas HTML pero no comillas, como esperan las pruebas
    return htmlspecialchars(trim($input), ENT_NOQUOTES, 'UTF-8');
}

/**
 * Verifica si la solicitud es AJAX
 */
function is_ajax_request()
{

    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Devuelve una respuesta JSON
 */
function json_response($data, $status = 200)
{

    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Actualizo base_url para usar BASE_URL definida en init.php
function base_url($path = '')
{
    $base = defined('BASE_URL') ? BASE_URL : '/';
    $path = $path ? '/' . ltrim($path, '/') : '';
    return rtrim($base, '/') . $path;
}

// Nuevos helpers movidos desde init.php
function assets_url($path = '')
{
    return base_url('assets/' . ltrim($path, '/'));
}

function styles_url($path = '')
{
    return base_url('styles/' . ltrim($path, '/'));
}

function script_url($path = '')
{
    return base_url(ltrim($path, '/'));
}

function view_path($view)
{
    return __DIR__ . '/../../views/' . ltrim($view, '/') . '.php';
}

function redirect($path = '')
{
    header('Location: ' . base_url($path));
    exit;
}

function isAuthenticated()
{
    return isset($_SESSION['user']) && isset($_SESSION['user']['id']);
}

function hasRole($roles)
{
    if (!isAuthenticated()) {
        return false;
    }
    $userRole = $_SESSION['user']['role'] ?? '';
    return in_array($userRole, (array) $roles, true);
}

function rate_limit($key, $max = 30, $windowSeconds = 60)
{
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

function handle_route($uri)
{
    // Quitar /algorix/ del inicio
    $uri = str_replace('/algorix/', '', $uri);
    $uri = trim($uri, '/');

    if (empty($uri)) {
        return 'home';
    }

    $parts = explode('/', $uri);
    $base = $parts[0];

    switch ($base) {
        case 'auth':
            $action = $parts[1] ?? 'login';
            return 'auth/' . $action;
        case 'student':
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
        default:
            return $uri;
    }
}

function checkCsrfToken(): void
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method === 'GET') {
        return;
    }
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

function requireAuthRole($role)
{
    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['error' => 'unauthenticated']);
        exit;
    }
    $r = $_SESSION['user']['role'] ?? '';
    if ($r !== $role) {
        http_response_code(403);
        echo json_encode(['error' => 'forbidden']);
        exit;
    }
}
