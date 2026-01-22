<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

header('Content-Type: application/json; charset=utf-8');

session_start();

require_once '../controller/controller.php';

// Obtener datos del JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$error = false;
$profile_code = $data['profile_code'] ?? '';
$email = $data['email'] ?? '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = true;
}
$username = $data['username'] ?? '';
$telephone = $data['telephone'] ?? '';
if (!filter_var($telephone, FILTER_VALIDATE_INT) && !$error) {
    $error = true;
}
$name = $data['name'] ?? '';
$surname = $data['surname'] ?? '';
$current_account = $data['current_account'] ?? '';

if (!$error) {
    $controller = new controller();
    $modify = $controller->modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code);
}

if ($error) {
    echo json_encode([
        'resultado' => 'Invalid syntax in one of the fields.',
        'status' => http_response_code(400),
        'success' => false
    ]);
} else {
    if ($modify) {
        // Actualizar la sesión con los nuevos datos
        if (isset($_SESSION['user_data'])) {
            $_SESSION['user_data']['NAME_'] = $name;
            $_SESSION['user_data']['SURNAME'] = $surname;
            $_SESSION['user_data']['EMAIL'] = $email;
            $_SESSION['user_data']['USER_NAME'] = $username;
            $_SESSION['user_data']['TELEPHONE'] = $telephone;
            $_SESSION['user_data']['CURRENT_ACCOUNT'] = $current_account;
        }
        
        echo json_encode([
            'message' => 'Admin modified correctly',
            'status' => http_response_code(200),
            'success' => true
        ]);
    } else {
        echo json_encode([
            'error' => 'Error modifying the admin',
            'status' => http_response_code(400),
            'success' => false
        ]);
    }
}
?>