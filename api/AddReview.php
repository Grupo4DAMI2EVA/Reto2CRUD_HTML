<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

header('Content-Type: application/json; charset=utf-8');

require_once '../controller/controller.php';

$error = false;
$comment = filter_input(INPUT_POST, "coment", FILTER_UNSAFE_RAW);
$rating = $_GET['rating'] ?? '';
if (!filter_input(INPUT_POST, "rating", FILTER_VALIDATE_FLOAT)) {
    $error = true;
}
$user_code = $_GET['profile_code'] ?? '';
//Currently unsettable
$videogame_code = $_GET['videogame_code'] ?? '';

if (!$error) {
    $controller = new controller();
    $addReview = $controller->add_review($comment, $rating, $user_code, $videogame_code);
}

if ($error) {
    echo json_encode([
        'result' => 'Invalid syntax in one of the fields.',
        'status' => http_response_code(400),
        'success' => false
    ]);
} else {
    if ($addReview) {
        echo json_encode([
            'result' => 'Review added correctly',
            'status' => http_response_code(203),
            'success' => true
        ]);
    } else {
        echo json_encode([
            'error' => 'Error adding the review',
            'status' => http_response_code(400),
            'success' => false
        ]);
    }
}
?>