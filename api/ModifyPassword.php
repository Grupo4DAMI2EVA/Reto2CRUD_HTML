<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../controller/controller.php';

$input = json_decode(file_get_contents('php://input'), true);
$profile_code = $input['profile_code'] ?? '';
$currentPassword = $input['currentPassword'] ?? '';
$newPassword = $input['newPassword'] ?? '';

$controller = new controller();
$user = $controller->get_user_by_profile_code($profile_code);

if (!$user) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'User not found']);
    exit;
}

$isValid = $controller->checkUser($user['USER_NAME'], $currentPassword);

if (is_string($isValid)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'That is not your current password']);
    exit;
}

$modify = $controller->modifyPassword($profile_code, $newPassword);

if ($modify) {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Password modified correctly']);
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Error modifying the password']);
}
?>