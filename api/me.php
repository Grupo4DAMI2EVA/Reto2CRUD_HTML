<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

header("Content-Type: application/json");

session_start();

if (!isset($_SESSION['logeado']) || !$_SESSION['logeado']) {
    http_response_code(401);
    echo json_encode(["error" => "No autorizado"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Devolver los datos del usuario
echo json_encode($_SESSION['user_data'], JSON_UNESCAPED_UNICODE);
?>