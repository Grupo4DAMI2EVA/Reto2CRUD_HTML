<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

header('Content-Type: application/json; charset=utf-8');

session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['logeado']) || !$_SESSION['logeado']) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Usuario no autenticado.'
    ]);
    exit;
}

require_once '../controller/controller.php';

try {
    // Obtener datos de la petición
    $data = json_decode(file_get_contents('php://input'), true);

    // Si no hay JSON, intentar con POST normal
    if (json_last_error() !== JSON_ERROR_NONE) {
        $data = $_POST;
    }

    // Validar datos requeridos
    if (!isset($data['comment']) || !isset($data['rating']) || !isset($data['videogame_code'])) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Faltan campos requeridos: comment, rating, videogame_code',
            'success' => false
        ]);
        exit;
    }

    $comment = trim($data['comment']);
    $rating = floatval($data['rating']);
    $videogame_code = intval($data['videogame_code']);

    // Obtener el PROFILE_CODE de la sesión (no USER_CODE)
    $profile_code = $_SESSION['user_data']['PROFILE_CODE']
        ?? $_SESSION['user_data']['USER_CODE']
        ?? null;

    if (!$profile_code) {
        http_response_code(400);
        echo json_encode([
            'session_data' => $_SESSION['user_data'] ?? 'No hay datos de sesión',
            'error' => 'No se pudo identificar al usuario (profile_code no encontrado)',
            'success' => false
        ]);
        exit;
    }

    // Validar rating (0.5 a 5.0)
    if ($rating < 0.5 || $rating > 5.0) {
        http_response_code(400);
        echo json_encode([
            'error' => 'El rating debe estar entre 0.5 y 5.0',
            'success' => false
        ]);
        exit;
    }

    // Validar comentario (máximo 500 caracteres)
    if (strlen($comment) > 500) {
        http_response_code(400);
        echo json_encode([
            'error' => 'El comentario no puede exceder 500 caracteres',
            'success' => false
        ]);
        exit;
    }

    $controller = new controller();
    $addReview = $controller->add_review($comment, $rating, $profile_code, $videogame_code);

    if ($addReview > 0) {
        echo json_encode([
            'message' => 'Reseña añadida correctamente',
            'review_id' => $addReview,
            'success' => true
        ]);
    } else if ($addReview === false) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Ya tienes una reseña para este juego',
            'success' => false
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'error' => 'Error al añadir la reseña',
            'success' => false
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error del servidor: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'success' => false
    ]);
}
?>