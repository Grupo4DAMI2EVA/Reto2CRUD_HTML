<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

header('Content-Type: application/json; charset=utf-8');

session_start();

// Verificar sesión
if (!isset($_SESSION['logeado']) || !$_SESSION['logeado']) {
    echo json_encode([
        'error' => 'No autorizado',
        'status' => http_response_code(401),
        'success' => false
    ]);
    exit;
}

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'error' => 'Método no permitido',
        'status' => http_response_code(405),
        'success' => false
    ]);
    exit;
}

// Obtener datos del JSON
$data = json_decode(file_get_contents("php://input"), true);

// Validar que hay items en el carrito
if (!isset($data['items']) || !is_array($data['items']) || empty($data['items'])) {
    echo json_encode([
        'error' => 'El carrito está vacío',
        'status' => http_response_code(400),
        'success' => false
    ]);
    exit;
}

$profile_code = $_SESSION['user_data']['PROFILE_CODE'] ?? null;

if (!$profile_code) {
    echo json_encode([
        'error' => 'Error al obtener información del usuario',
        'status' => http_response_code(400),
        'success' => false
    ]);
    exit;
}

require_once '../controller/controller.php';

$controller = new controller();
$result = $controller->processPurchase($profile_code, $data['items']);

if (is_array($result) && isset($result['success']) && $result['success']) {
    // Limpiar el carrito de la sesión
    $_SESSION['cart'] = [];

    // Actualizar datos del usuario en sesión
    $user = $controller->get_user_by_profile_code($profile_code);
    if ($user) {
        $_SESSION['user_data']['BALANCE'] = $user['BALANCE'];
    }

    echo json_encode([
        'message' => $result['message'] ?? 'Compra realizada con éxito',
        'status' => http_response_code(200),
        'success' => true
    ]);
} else {
    // Devolver error específico
    $errorType = is_array($result) && isset($result['error_type']) ? $result['error_type'] : 'unknown';
    $errorMessage = is_array($result) && isset($result['message']) ? $result['message'] : 'Error al procesar la compra';

    $response = [
        'error' => $errorMessage,
        'error_type' => $errorType,
        'status' => http_response_code(400),
        'success' => false
    ];

    // Añadir información adicional si está disponible
    if (is_array($result)) {
        if (isset($result['balance']))
            $response['balance'] = $result['balance'];
        if (isset($result['required']))
            $response['required'] = $result['required'];
        if (isset($result['needed']))
            $response['needed'] = $result['needed'];
    }
    echo json_encode($response);
}
?>