<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

header('Content-Type: application/json; charset=utf-8');

require_once '../controller/controller.php';

$code = $_GET['code'] ?? '';
$name = $_GET['name'] ?? '';
$platform = $_GET['platform'] ?? '';
$company = $_GET['company'] ?? '';
$stock = $_GET['stock'] ?? '';
$genre = $_GET['genre'] ?? '';
$price = $_GET['price'] ?? '';
$pegi = $_GET['pegi'] ?? '';
$releaseDate = $_GET['releaseDate'] ?? '';

try {
    $controller = new controller();
    $modify = $controller->modify_videogame(
        $code,
        $price,
        $name,
        $platform,
        $genre,
        $pegi,
        $stock,
        $company,
        $releaseDate
    );

    if ($modify) {
        echo json_encode([
            'resultado' => 'El videojuego ha sido modificado correctamente.',
            'status' => http_response_code(200),
            'exito' => true
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'resultado' => 'No se ha creado correctamente el videojuego.',
            'status' => http_response_code(400),
            'exito' => false
        ]);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'error' => 'Error del servidor: ' . $e->getMessage(),
        'status' => http_response_code(500),
        'exito' => false
    ]);
}
?>