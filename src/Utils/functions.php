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
function render_view($view, $data = []) {
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
function render_error($code, $message) {
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
function sanitize_input($input) {
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
function is_ajax_request() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Devuelve una respuesta JSON
 */
function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Genera una URL base para enlaces internos
 */
function base_url($path = '') {
    $base = '/';
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}