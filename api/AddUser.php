<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

// Iniciar sesión
session_start();

require_once '../controller/controller.php';
header('Content-Type: application/json; charset=utf-8');

// Solo leer el input JSON, no mezclar con POST
$input = json_decode(file_get_contents('php://input'), true);

// CORREGIDO: Obtener username del JSON, no de POST
$username = $input['username'] ?? '';
$pswd1 = $input['pswd1'] ?? '';
$pswd2 = $input['pswd2'] ?? '';

$response = ["success" => false];

// Validación básica
if (empty($username) || empty($pswd1)) {
    echo json_encode([
        'error' => 'Usuario y contraseña son obligatorios',
        'status' => http_response_code(400),
        'success' => false
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $controller = new controller();
    // Añadir logs para depuración
    error_log("Intentando crear usuario: $username");

    $user = $controller->create_user($username, $pswd1);

    if ($user) {
        // Guardar user en la sesión
        $_SESSION['logeado'] = true;
        $_SESSION['tipo'] = 'user';
        $_SESSION['user_data'] = $user;
        $_SESSION['username'] = $username;

        echo json_encode([
            'message' => "The user was created properly.",
            'result' => $user,
            'status' => http_response_code(201),
            'success' => true
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'error' => 'No se ha creado correctamente el usuario',
            'status' => http_response_code(400),
            'success' => false
        ]);
    }
} catch (Exception $e) {
    error_log("Error en AddUser.php: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
    echo json_encode([
        'error' => 'Error del servidor: ' . $e->getMessage(),
        'status' => http_response_code(500),
        'success' => false
    ]);
}
?>