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

// Inicializar carrito si no existe
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Obtener información completa de los videojuegos en el carrito
require_once '../controller/controller.php';
$controller = new controller();
$allVideogames = $controller->get_all_videogames();

$cartItems = [];

foreach ($_SESSION['cart'] as $cartItem) {
    $videogame_code = $cartItem['videogame_code'];
    $quantity = $cartItem['quantity'];

    // Buscar el videojuego en la lista completa
    foreach ($allVideogames as $game) {
        if ($game['VIDEOGAME_CODE'] == $videogame_code) {
            $cartItems[] = [
                'id' => $game['VIDEOGAME_CODE'],
                'videogame_code' => $game['VIDEOGAME_CODE'],
                'name' => $game['NAME_'],
                'price' => floatval($game['PRICE']),
                'qty' => $quantity
            ];
            break;
        }
    }
}

echo json_encode($cartItems, JSON_UNESCAPED_UNICODE);
?>