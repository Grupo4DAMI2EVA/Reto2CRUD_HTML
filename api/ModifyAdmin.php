<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

header('Content-Type: application/json; charset=utf-8');

require_once '../controller/controller.php';

$error = false;
$profile_code = $_GET['profile_code'] ?? '';
$email = $_GET['email'] ?? '';
if (!filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL)) {
    $error = true;
}
$username = filter_input(INPUT_POST, "username", FILTER_UNSAFE_RAW);
$telephone = $_GET['telephone'] ?? '';
if (!filter_input(INPUT_POST, "telephone", FILTER_VALIDATE_INT) && !$error) {
    $error = true;
}
$name = filter_input(INPUT_POST, "name", FILTER_UNSAFE_RAW);
$surname = filter_input(INPUT_POST, "surname", FILTER_UNSAFE_RAW);
$current_account = $_GET['current_account'] ?? '';

if (!$error) {
    $controller = new controller();
    $modify = $controller->modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code);
}

if ($error) {
    echo json_encode([
        'resultado' => 'Invalid syntax in one of the fields.',
        'status' => http_response_code(400),
        'exito' => false
    ]);
} else {
    if ($modify) {
        echo json_encode([
            'success' => true,
            'message' => 'Admin modified correctly',
            'status' => http_response_code(200)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Error modifying the admin',
            'status' => http_response_code(400)
        ]);
    }
}
?>