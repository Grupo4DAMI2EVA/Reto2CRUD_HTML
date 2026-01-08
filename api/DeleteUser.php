<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

header('Content-Type: application/json; charset=utf-8');

session_start();

// Verificar que hay sesión activa
if (!isset($_SESSION['logeado']) || !$_SESSION['logeado']) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Leer id desde JSON POST, POST form-data, o fallback GET
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$id = $data['id'] ?? $_POST['id'] ?? $_GET['id'] ?? null;

// Validación básica
if ($id === null || !filter_var($id, FILTER_VALIDATE_INT)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID inválido'], JSON_UNESCAPED_UNICODE);
    exit;
}

$id = intval($id);
$selfId = isset($_SESSION['user_data']['PROFILE_CODE']) ? intval($_SESSION['user_data']['PROFILE_CODE']) : null;
$isAdmin = (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin');

// Permisos:
// - Si es admin, puede borrar cualquier cuenta (incluida la suya)
// - Si no es admin, sólo puede borrar su propia cuenta
if (!$isAdmin && ($selfId === null || $selfId !== $id)) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado. Sólo puedes borrar tu propia cuenta.'], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once '../controller/controller.php';
$controller = new controller();
$result = $controller->delete_user($id);

if ($result) {
    // Si el usuario se ha eliminado a sí mismo, cerrar sesión
    if ($selfId !== null && $selfId === $id) {
        session_unset();
        session_destroy();
        echo json_encode(['result' => true, 'message' => 'Cuenta eliminada y sesión cerrada.'], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['result' => true], JSON_UNESCAPED_UNICODE);
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'User not found'], JSON_UNESCAPED_UNICODE);
}
?>