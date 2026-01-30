<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

header("Content-Type: application/json");

session_start();

require_once '../controller/controller.php';

$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

$controller = new controller();
$user = $controller->loginUser($username, $password);

if (is_null($user)) {
    $admin = $controller->loginAdmin($username, $password);
    if (is_null($admin)) {
        echo json_encode([
            "error" => 'El nombre de usuario o contraseña son incorrectos.',
            'status' => http_response_code(400),
            'success' => false
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // Guardar admin en la sesión
        $_SESSION['logeado'] = true;
        $_SESSION['tipo'] = 'admin';
        $_SESSION['user_data'] = $admin;
        $_SESSION['username'] = $username;
        echo json_encode([
            "result" => $admin,
            'status' => http_response_code(200),
            'success' => true
        ], JSON_UNESCAPED_UNICODE);
    }
} else {
    // Guardar user en la sesión
    $_SESSION['logeado'] = true;
    $_SESSION['tipo'] = 'user';
    $_SESSION['user_data'] = $user;
    $_SESSION['username'] = $username;
    echo json_encode([
        "result" => $user,
        'status' => http_response_code(200),
        'success' => true
    ], JSON_UNESCAPED_UNICODE);
}
?>