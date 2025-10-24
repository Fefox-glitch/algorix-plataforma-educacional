<?php
namespace App\Core;

/**
 * Router simple para la aplicación
 */

/**
 * Clase Router para manejar las rutas de la aplicación
 */
class Router {
    private $routes = [];
    private $notFoundCallback;
    
    /**
     * Registra una ruta GET
     * 
     * @param string $route Ruta a registrar
     * @param mixed $callback Función o método a ejecutar
     */
    public function get($route, $callback) {
        $this->routes['GET'][$route] = $callback;
    }
    
    /**
     * Registra una ruta para cualquier método HTTP
     * 
     * @param string $route Ruta a registrar
     * @param mixed $callback Función o método a ejecutar
     */
    public function any($route, $callback) {
        $this->routes['GET'][$route] = $callback;
        $this->routes['POST'][$route] = $callback;
    }
    
    /**
     * Registra un manejador para rutas no encontradas
     * 
     * @param callable $callback Función a ejecutar cuando no se encuentra la ruta
     */
    public function notFound($callback) {
        $this->notFoundCallback = $callback;
    }
    
    /**
     * Despacha la solicitud a la ruta correspondiente
     * 
     * @param string $uri URI a despachar
     * @return mixed Resultado de la ejecución de la ruta
     */
    public function dispatch($uri) {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Normalizar URI
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }
        
        // Verificar si existe la ruta para el método actual
        if (isset($this->routes[$method][$uri])) {
            $callback = $this->routes[$method][$uri];
            return $this->executeCallback($callback);
        }
        
        // Si no se encontró la ruta, ejecutar el manejador de no encontrado
        if ($this->notFoundCallback) {
            return call_user_func($this->notFoundCallback);
        }
        
        // Si no hay manejador de no encontrado, mostrar error 404 por defecto
        header("HTTP/1.0 404 Not Found");
        echo "404 Not Found";
    }
    
    /**
     * Ejecuta el callback de una ruta
     * 
     * @param mixed $callback Función o método a ejecutar
     * @return mixed Resultado de la ejecución
     */
    private function executeCallback($callback) {
        if (is_callable($callback)) {
            return call_user_func($callback);
        }
        
        if (is_array($callback) && count($callback) === 2) {
            $controller = $callback[0];
            $method = $callback[1];
            
            // Si el controlador comienza con \, es un nombre de clase completo
            if (strpos($controller, '\\') === 0) {
                $controller = 'App\\Controllers' . $controller;
            }
            
            if (class_exists($controller)) {
                $instance = new $controller();
                if (method_exists($instance, $method)) {
                    return call_user_func([$instance, $method]);
                }
            }
        }
        
        // Si no se pudo ejecutar el callback, mostrar error 500
        header("HTTP/1.0 500 Internal Server Error");
        echo "500 Internal Server Error: Invalid route callback";
    }
}

/**
 * Maneja las rutas de la aplicación
 */
function handle_route($uri) {
    // Inicializar el router
    $router = new Router();
    
    // Registrar rutas
    $router->get('', function() {
        $pageTitle = 'Bienvenido a Algorix';
        $content = renderView('home');
        include __DIR__ . '/../../views/shared/layout.php';
    });
    
    $router->get('/', function() {
        $pageTitle = 'Bienvenido a Algorix';
        $content = renderView('home');
        include __DIR__ . '/../../views/shared/layout.php';
    });
    
    $router->any('/auth/login', ['\AuthController', 'login']);
    $router->any('/auth/register', ['\AuthController', 'register']);
    
    // Grupo de rutas protegidas para admin
    $router->get('/admin/dashboard', function() {
        if (!isAuthenticated() || !hasRole('admin')) {
            redirect('auth/login');
            return;
        }
        
        $pageTitle = 'Panel de Administrador';
        $userRole = 'admin';
        $content = renderView('admin/dashboard');
        include __DIR__ . '/../../views/shared/layout.php';
    });
    
    // Grupo de rutas protegidas para profesor
    $router->get('/teacher/dashboard', function() {
        if (!isAuthenticated() || !hasRole('teacher')) {
            redirect('auth/login');
            return;
        }
        
        $pageTitle = 'Panel de Profesor';
        $userRole = 'teacher';
        $content = renderView('teacher/dashboard');
        include __DIR__ . '/../../views/shared/layout.php';
    });
    
    // Grupo de rutas protegidas para estudiante
    $router->get('/student/dashboard', function() {
        if (!isAuthenticated() || !hasRole('student')) {
            redirect('auth/login');
            return;
        }
        
        $pageTitle = 'Panel de Estudiante';
        $userRole = 'student';
        $content = renderView('student/dashboard');
        include __DIR__ . '/../../views/shared/layout.php';
    });
    
    // Manejador de ruta no encontrada
    $router->notFound(function() {
        header("HTTP/1.0 404 Not Found");
        $pageTitle = 'Página no encontrada';
        $content = '<div class="error-container">
            <h1>404</h1>
            <p>Lo sentimos, la página que buscas no existe.</p>
            <a href="/" class="btn btn-primary">Volver al inicio</a>
        </div>';
        include __DIR__ . '/../../views/shared/layout.php';
    });
    
    // Despachar la solicitud
    $router->dispatch($uri);
}