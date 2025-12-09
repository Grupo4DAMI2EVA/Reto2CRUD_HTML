<?php
session_start();

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado'], JSON_UNESCAPED_UNICODE);
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once '../controller/controller.php';

$input = json_decode(file_get_contents('php://input'), true);
$profile_code = $input['profile_code'] ?? '';
$password = $input['password'] ?? '';


$controller = new controller();
$modify = $controller->modifyPassword($profile_code, $password);

if ($modify) {
    echo json_encode(['success' => true, 'message' => 'Password modified correctly']);
} else {
    echo json_encode(['success' => false, 'error' => 'Error modifying the password']);
}
?>