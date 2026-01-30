<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

header("Content-Type: application/json; charset=utf-8");

session_start();

if (!isset($_SESSION['logeado']) || !$_SESSION['logeado']) {
    http_response_code(401);
    echo json_encode([
        'error' => 'Usuario no autenticado.',
        'success' => false
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    require_once '../controller/controller.php';

    $controller = new controller();

    // Obtener profile_code del parámetro de la petición (GET o POST)
    $profile_code = $_GET['profile_code'] ?? $_POST['profile_code'] ?? null;

    if (!$profile_code) {
        // Si no viene en la petición, usar el de sesión como fallback
        $profile_code = $_SESSION['user_data']['USER_CODE']
            ?? $_SESSION['user_data']['PROFILE_CODE']
            ?? null;
    }

    if (!$profile_code) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Código de usuario no válido. Sesión: ' . json_encode($_SESSION['user_data'] ?? 'No hay datos'),
            'success' => false
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $reviews = $controller->get_user_reviews($profile_code);

    if ($reviews !== false) {
        http_response_code(200);
        echo json_encode([
            'result' => $reviews,
            'success' => true,
            'profile_code_used' => $profile_code // Para debugging
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(400);
        echo json_encode([
            'error' => 'Error al obtener las reseñas. Método devolvió false.',
            'success' => false,
            'profile_code_used' => $profile_code
        ], JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error del servidor: ' . $e->getMessage(),
        'success' => false,
        'trace' => $e->getTraceAsString()
    ], JSON_UNESCAPED_UNICODE);
}
?>