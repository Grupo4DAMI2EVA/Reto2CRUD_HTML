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

// Validar datos requeridos
if (!isset($data['videogame_code']) || !isset($data['quantity'])) {
    echo json_encode([
        'error' => 'Código de videojuego y cantidad requeridos',
        'status' => http_response_code(400),
        'success' => false
    ]);
    exit;
}

$videogame_code = intval($data['videogame_code']);
$quantity = intval($data['quantity']);

// Validar que la cantidad sea positiva
if ($quantity <= 0) {
    echo json_encode([
        'error' => 'La cantidad debe ser mayor a 0',
        'status' => http_response_code(400),
        'success' => false
    ]);
    exit;
}

// Inicializar carrito en sesión si no existe
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Buscar si el videojuego ya está en el carrito
$found = false;
foreach ($_SESSION['cart'] as &$item) {
    if ($item['videogame_code'] == $videogame_code) {
        $item['quantity'] += $quantity;
        $found = true;
        break;
    }
}

// Si no está en el carrito, añadirlo
if (!$found) {
    $_SESSION['cart'][] = [
        'videogame_code' => $videogame_code,
        'quantity' => $quantity
    ];
}

echo json_encode([
    'message' => 'Producto añadido al carrito correctamente',
    'status' => http_response_code(200),
    'success' => true
], JSON_UNESCAPED_UNICODE);
?>