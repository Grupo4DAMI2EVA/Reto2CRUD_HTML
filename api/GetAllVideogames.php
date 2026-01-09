<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

header("Content-Type: application/json");

require_once '../controller/controller.php';

$controller = new controller();
$videogames = $controller->get_all_videogames();

if ($videogames) {
    echo json_encode([
        'resultado' => $videogames
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['error' => 'No se ha encontrado ningun juego.']);
}
?>