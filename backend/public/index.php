<?php
// If the requested file exists (asset), let the server handle it:
$path = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path !== __FILE__ && file_exists($path)) {
    return false; // serve the file directly
}
// Otherwise, load the built SPA index.html
readfile(__DIR__ . '/index.html');

