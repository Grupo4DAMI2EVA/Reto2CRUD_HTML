<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

header('Content-Type: application/json; charset=utf-8');

session_start();

// 1. Verificar que hay sesión activa
if (!isset($_SESSION['logeado']) || !$_SESSION['logeado']) {
    http_response_code(401);
    echo json_encode([
        'error' => 'No autorizado',
        'status' => 401,
        'exito' => false
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 2. Obtener ID (JSON POST, POST form-data, o GET)
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$id = $data['id'] ?? $_POST['id'] ?? $_GET['id'] ?? null;

// 3. Validación de ID
if ($id === null || !filter_var($id, FILTER_VALIDATE_INT)) {
    http_response_code(400);
    echo json_encode([
        'error' => 'ID inválido',
        'status' => 400,
        'exito' => false
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$id = intval($id);
$selfId = isset($_SESSION['user_data']['PROFILE_CODE']) ? intval($_SESSION['user_data']['PROFILE_CODE']) : null;
$isAdmin = (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin');

// 4. Permisos
if (!$isAdmin && ($selfId === null || $selfId !== $id)) {
    http_response_code(403);
    echo json_encode([
        'error' => 'Acceso denegado. Sólo puedes borrar tu propia cuenta.',
        'status' => 403,
        'exito' => false
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once '../controller/controller.php';
$controller = new controller();
$result = $controller->delete_user($id);

// 5. Manejo del resultado
if ($result) {
    // Definimos respuesta de éxito
    $httpCode = 200;
    $response = [
        'result' => true,
        'message' => 'Usuario eliminado correctamente.',
        'status' => $httpCode,
        'exito' => true
    ];

    // Si el usuario se ha eliminado a sí mismo, cerrar sesión
    if ($selfId !== null && $selfId === $id) {
        session_unset();
        session_destroy();
        $response['message'] = 'Cuenta propia eliminada y sesión cerrada.';
    }

    http_response_code($httpCode);
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(404);
    echo json_encode([
        'result' => false,
        'error' => 'No se pudo eliminar el usuario. Es posible que no exista.',
        'status' => 404,
        'exito' => false
    ], JSON_UNESCAPED_UNICODE);
}
?>