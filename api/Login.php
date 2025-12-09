<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

header("Content-Type: application/json");

require_once '../controller/controller.php';

$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

$controller = new controller();
$user = $controller->loginUser($username, $password);

if (is_null($user)) {
    $admin = $controller->loginAdmin($username, $password);
    if (is_null($admin)) {
        echo json_encode(["error" => 'El nombre de usuario o contraseña son incorrectos.'], JSON_UNESCAPED_UNICODE);
    } else {
        $_SESSION['user'] = $admin;
        $_SESSION['is_admin'] = true;
        echo json_encode(["resultado" => $admin], JSON_UNESCAPED_UNICODE);
    }
} else {
    $_SESSION['user'] = $user;
    $_SESSION['is_admin'] = false;
    echo json_encode(["resultado" => $user], JSON_UNESCAPED_UNICODE);
}
?>