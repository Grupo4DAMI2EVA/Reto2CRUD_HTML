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
$gender = $_GET['gender'] ?? '';
$card_no = $_GET['card_no'] ?? '';

$controller = new controller();
$modify = $controller->modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code);

if ($modify) {
    echo json_encode(['success' => true, 'message' => 'User modified correctly']);
} else {
    echo json_encode(['success' => false, 'error' => 'Error modifying the user']);
}
?>