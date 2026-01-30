<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

header('Content-Type: application/json; charset=utf-8');

require_once '../controller/controller.php';

$controller = new controller();
$users = $controller->get_all_users();

if ($users) {
    echo json_encode([
        'resultado' => $users,
        'status' => http_response_code(200),
        'exito' => true
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'error' => 'No se ha encontrado usuarios',
        'status' => http_response_code(400),
        'exito' => false
    ]);
}
?>