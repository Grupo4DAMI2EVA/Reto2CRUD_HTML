<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    ini_set('log_errors', 1);
    ini_set('error_log', 'php_error.log');

    header('Content-Type: application/json; charset=utf-8');

    require_once '../controller/controller.php';

    $profile_code = $_GET['profile_code'] ?? '';
    $user_id = $_GET['user_id'] ?? '';
    $controller = new controller();
    $delete = $controller->deleteFromKart($user_id, $profile_code);
    if ($delete) {
        echo json_encode(['success' => true, 'message' => 'Product removed from cart successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error removing product from cart']);
    }
?>