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

// Solo permitir POST para mayor seguridad
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido',
        'status' => http_response_code(405)
    ]);
    exit;
}

// Obtener datos del JSON
$data = json_decode(file_get_contents("php://input"), true);

// Validar datos requeridos
if (!isset($data['profile_code'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Código de perfil requerido',
        'status' => http_response_code(400)
    ]);
    exit;
}

// Validar que todos los campos necesarios estén presentes
$required_fields = ['email', 'username', 'telephone', 'name', 'surname', 'gender', 'card_no'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty(trim($data[$field]))) {
        echo json_encode([
            'success' => false,
            'error' => "El campo $field es requerido",
            'status' => http_response_code(400)
        ]);
        exit;
    }
}

// Extraer y sanitizar datos
$profile_code = trim($data['profile_code']);
$email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
$username = trim($data['username']);
$telephone = trim($data['telephone']);
$name = trim($data['name']);
$surname = trim($data['surname']);
$gender = trim($data['gender']);
$card_no = trim($data['card_no']);

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'error' => 'Email no válido',
        'status' => http_response_code(400)
    ]);
    exit;
}

// Verificar permisos
if ($_SESSION['tipo'] === 'user') {
    // Usuario normal solo puede modificar su propio perfil
    if ($_SESSION['user_data']['PROFILE_CODE'] != $profile_code) {
        echo json_encode([
            'success' => false,
            'error' => 'No tienes permiso para modificar este perfil',
            'status' => http_response_code(403)
        ]);
        exit;
    }
}

require_once '../controller/controller.php';

$controller = new controller();
$modify = $controller->modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code);

if ($error) {
    echo json_encode([
        'resultado' => 'Invalid syntax in one of the fields.',
        'status' => http_response_code(400),
        'exito' => false
    ]);
} else {
    if ($modify) {
        // Actualizar datos en sesión si el usuario modifica su propio perfil
        if ($_SESSION['user_data']['PROFILE_CODE'] == $profile_code) {
            $_SESSION['user_data']['EMAIL'] = $email;
            $_SESSION['user_data']['USER_NAME'] = $username;
            $_SESSION['user_data']['TELEPHONE'] = $telephone;
            $_SESSION['user_data']['NAME_'] = $name;
            $_SESSION['user_data']['SURNAME'] = $surname;
            $_SESSION['user_data']['GENDER'] = $gender;
            $_SESSION['user_data']['CARD_NO'] = $card_no;
            $_SESSION['username'] = $username;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Usuario modificado correctamente',
            'status' => http_response_code(200)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Error modificando el usuario. Puede que el email o username ya existan.',
            'status' => http_response_code(400)
        ]);
    }
}
?>