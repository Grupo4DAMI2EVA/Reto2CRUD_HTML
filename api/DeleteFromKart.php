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
        'success' => false,
        'error' => 'No autorizado',
        'status' => http_response_code(401),
        'exito' => false
    ]);
    exit;
}

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido',
        'status' => http_response_code(405),
        'exito' => false
    ]);
    exit;
}

// Obtener datos del formulario o JSON
$id = null;
if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
} else {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = isset($data['id']) ? intval($data['id']) : null;
}

if ($id === null) {
    echo json_encode([
        'success' => false,
        'error' => 'ID del producto requerido',
        'status' => http_response_code(400),
        'exito' => false
    ]);
    exit;
}

// Inicializar carrito si no existe
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Buscar y eliminar el item del carrito
$found = false;
foreach ($_SESSION['cart'] as $key => $item) {
    if ($item['videogame_code'] == $id) {
        unset($_SESSION['cart'][$key]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindexar array
        $found = true;
        break;
    }
}

if ($found) {
    echo json_encode([
        'success' => true,
        'message' => 'Producto eliminado del carrito correctamente',
        'status' => http_response_code(200),
        'exito' => true
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Producto no encontrado en el carrito',
        'status' => http_response_code(404),
        'exito' => false
    ]);
}
?>