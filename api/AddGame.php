<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

header('Content-Type: application/json; charset=utf-8');

require_once '../controller/controller.php';

$error = false;
$name = filter_input(INPUT_POST, "name", FILTER_UNSAFE_RAW);
$platform = $_GET['platform'] ?? '';
$company = $_GET['company'] ?? '';
$stock = $_GET['stock'] ?? '';
if (!filter_input(INPUT_POST, "stock", FILTER_VALIDATE_INT)) {
    $error = true;
}

$genre = $_GET['genre'] ?? '';
$price = $_GET['price'] ?? '';
if (!filter_input(INPUT_POST, "price", FILTER_VALIDATE_FLOAT) && !$error) {
    $error = true;
}

$pegi = $_GET['pegi'] ?? '';
$releaseDate = $_GET['releaseDate'] ?? '';
if (!$error) {
    $error = validate_date($releaseDate, "y/m/d");
}

function validate_date($date, $format = "y/m/d")
{
    $d = DateTime::createFromFormat($format, $date);
    return $d->format($format) === $date;
}

try {
    $controller = new controller();
    $add = $controller->add_videogame(
        $price,
        $name,
        $platform,
        $genre,
        $pegi,
        $stock,
        $company,
        $releaseDate
    );

    if ($add > 0) {
        echo json_encode([
            'resultado' => 'El videojuego ha sido creado correctamente.',
            'status' => http_response_code(201),
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