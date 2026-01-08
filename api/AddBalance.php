<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

header("Content-Type: application/json; charset=utf-8");

session_start();

require_once '../controller/controller.php';

// Comprobar que el usuario está logueado
if (!isset($_SESSION['logeado']) || !$_SESSION['logeado'] || !isset($_SESSION['user_data'])) {
    http_response_code(401);
    echo json_encode(["error" => "No autorizado"], JSON_UNESCAPED_UNICODE);
    exit;
}

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$amount = $data['amount'] ?? 0;

// Validar cantidad
if (!is_numeric($amount) || $amount <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Cantidad no válida"], JSON_UNESCAPED_UNICODE);
    exit;
}

$profile_code = $_SESSION['user_data']['PROFILE_CODE'] ?? null;

if ($profile_code === null) {
    http_response_code(400);
    echo json_encode(["error" => "Perfil de usuario no encontrado"], JSON_UNESCAPED_UNICODE);
    exit;
}

$controller = new controller();
$result = $controller->addBalance($profile_code, $amount);

if ($result === false) {
    http_response_code(500);
    echo json_encode(["error" => "Error al actualizar el saldo"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Actualizar también la sesión con el nuevo balance
$_SESSION['user_data']['BALANCE'] = $result['BALANCE'];

echo json_encode([
    "success" => true,
    "new_balance" => $result['BALANCE']
], JSON_UNESCAPED_UNICODE);

?>


