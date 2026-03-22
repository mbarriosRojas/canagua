<?php
/**
 * Router para servidor PHP integrado (php -S)
 * Redirige todas las peticiones no estáticas a index.php
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Servir archivos estáticos si existen (CSS, JS, imágenes, etc.)
if ($uri !== '/' && $uri !== '' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Redirigir todo lo demás al enrutador principal
require __DIR__ . '/index.php';
