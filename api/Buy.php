<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

header('Content-Type: application/json; charset=utf-8');

require_once '../controller/controller.php';

$error = false;
$product_id = $_GET['product_id'] ?? '';
$user_id = $_GET['user_id'] ?? '';
$quantity = $_GET['quantity'] ?? 1;
if (!filter_input(INPUT_POST, "quantity", FILTER_VALIDATE_INT)) {
    $error = true;
}

$payment_method = $_GET['payment_method'] ?? '';

if (!$error) {
    $controller = new controller();
    $buy = $controller->buyProduct($product_id, $user_id, $quantity, $payment_method);
}

if ($error) {
    echo json_encode([
        'resultado' => 'Invalid syntax in one of the fields.',
        'status' => http_response_code(400),
        'exito' => false
    ]);
} else {
    if ($buy) {
        echo json_encode([
            'success' => true,
            'message' => 'Purchase completed successfully',
            'status' => http_response_code(200)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Error processing the purchase',
            'status' => http_response_code(400)
        ]);
    }
}
?>