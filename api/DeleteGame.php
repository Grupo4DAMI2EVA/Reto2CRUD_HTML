<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

header('Content-Type: application/json; charset=utf-8');

require_once '../controller/controller.php';

$code = $_GET['code'] ?? '';

try {
    $controller = new controller();
    $del = $controller->delete_videogame($code);

    if ($del) {
        echo json_encode([
            'result' => 'El videojuego ha sido eliminado correctamente.',
            'status' => http_response_code(204),
            'success' => true
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'result' => 'No se ha encontrado el videojuego.',
            'status' => http_response_code(404),
            'success' => false
        ]);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'error' => 'Error del servidor: ' . $e->getMessage(),
        'status' => http_response_code(500),
        'success' => false
    ]);
}
?>