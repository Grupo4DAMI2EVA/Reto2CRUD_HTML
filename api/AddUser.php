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

$input = json_decode(file_get_contents('php://input'), true);
$username = filter_input(INPUT_POST, "username", FILTER_UNSAFE_RAW);
$pswd1 = $input['pswd1'] ?? '';
$pswd2 = $input['pswd2'] ?? '';

try {

    $controller = new controller();
    $user = $controller->create_user($username, $pswd1);

    if ($user) {
        // Guardar user en la sesión
        $_SESSION['logeado'] = true;
        $_SESSION['tipo'] = 'user';
        $_SESSION['user_data'] = $user;
        $_SESSION['username'] = $username;

        echo json_encode([
            'message' => "The user was created properly.",
            'resultado' => $user,
            'exito' => true,
            'status' => http_response_code(203)
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'error' => 'No se ha creado correctamente el usuario',
            'exito' => false,
            'status' => http_response_code(400)
        ]);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'error' => 'Error del servidor: ' . $e->getMessage(),
        'exito' => false,
        'status' => http_response_code(500)
    ]);
}
?>