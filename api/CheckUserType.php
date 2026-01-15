<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

header("Content-Type: application/json");

// Iniciar sesión para verificar si el usuario ya está logueado
session_start();

// Si ya hay una sesión activa, usar los datos de la sesión
if (isset($_SESSION['logeado']) && $_SESSION['logeado'] === true) {
    $tipo = $_SESSION['tipo'] ?? '';

    if ($tipo === 'admin') {
        echo json_encode([
            "admin" => "admin",
            'message' => "The profile is an admin.",
            'status' => http_response_code(200),
            'exito' => true
        ], JSON_UNESCAPED_UNICODE);
    } else if ($tipo === 'user') {
        echo json_encode([
            "user" => "user",
            'message' => "The profile is an user.",
            'status' => http_response_code(200),
            'exito' => true
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            "error" => 'Tipo de usuario no válido en la sesión.',
            'status' => http_response_code(400),
            'exito' => false
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// Si no hay sesión, proceder con la verificación tradicional
require_once '../controller/controller.php';

$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

// Verificar que se proporcionaron credenciales
if (empty($username) || empty($password)) {
    echo json_encode([
        "error" => 'Se requieren nombre de usuario y contraseña.',
        'status' => http_response_code(400),
        'exito' => false
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$controller = new controller();
$type = $controller->checkUser($username, $password);

if ($type) {
    echo json_encode([
        "admin" => "admin",
        'message' => "The profile is an admin.",
        'status' => http_response_code(200),
        'exito' => true
    ], JSON_UNESCAPED_UNICODE);
} else if (!$type) {
    echo json_encode([
        "user" => "user",
        'message' => "The profile is an user.",
        'status' => http_response_code(200),
        'exito' => true
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        "error" => 'There was an error when processing the profile.',
        'status' => http_response_code(400),
        'exito' => false
    ], JSON_UNESCAPED_UNICODE);
}
?>