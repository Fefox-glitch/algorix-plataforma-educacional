<?php

/**
 * Funciones de renderizado para las vistas
 */

/**
 * Renderiza una vista con los datos proporcionados
 *
 * @param string $view Nombre de la vista a renderizar
 * @param array $data Datos para pasar a la vista
 * @return string HTML renderizado
 */
function renderView($view, $data = [])
{

    // Extraer los datos para que estén disponibles como variables en la vista
    extract($data);
// Iniciar el buffer de salida
    ob_start();
// Incluir el archivo de la vista
    $viewPath = APP_ROOT . '/views/' . $view . '.php';
    if (file_exists($viewPath)) {
        include $viewPath;
    } else {
        echo "Error: Vista '$view' no encontrada.";
    }

    // Obtener el contenido del buffer y limpiarlo
    $content = ob_get_clean();
    return $content;
}

/**
 * Renderiza el layout principal con el contenido proporcionado
 *
 * @param string $content Contenido HTML a insertar en el layout
 * @param string $title Título de la página
 * @return string HTML completo de la página
 */
function renderLayout($content, $title = 'Algorix')
{

    // Iniciar el buffer de salida
    ob_start();
// Variables esperadas por el layout compartido
    $pageTitle = $title;
// Incluir el archivo de layout canónico
    include APP_ROOT . '/views/shared/layout.php';
// Obtener el contenido del buffer y limpiarlo
    $output = ob_get_clean();
    return $output;
}
