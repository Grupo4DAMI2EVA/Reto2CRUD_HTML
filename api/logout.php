<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

header("Content-Type: application/json");

session_start();
session_destroy();

// Limpiar cookie 
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

echo json_encode([
    "result" => "Sesión cerrada correctamente",
    'status' => http_response_code(200),
    "success" => true
], JSON_UNESCAPED_UNICODE);
?>