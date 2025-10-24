<?php
/**
 * Renderiza una vista específica
 * @param string $view Ruta de la vista a renderizar
 * @param array $data Datos a pasar a la vista
 * @return string Contenido HTML renderizado
 */
function renderView($view, $data = []) {
    extract($data);
    ob_start();
    require __DIR__ . '/../../views/' . $view . '.php';
    return ob_get_clean();
}

/**
 * Renderiza el layout principal con el contenido proporcionado
 * @param string $content Contenido HTML a insertar en el layout
 * @return string Página HTML completa
 */
function renderLayout($content) {
    global $pageTitle;
    ob_start();
    require __DIR__ . '/../../views/shared/layout.php';
    return ob_get_clean();
}