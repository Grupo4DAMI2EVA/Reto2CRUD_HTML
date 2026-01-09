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
$coment = filter_input(INPUT_POST, "coment", FILTER_UNSAFE_RAW);
$rating = $_GET['rating'] ?? '';
if (!filter_input(INPUT_POST, "rating", FILTER_VALIDATE_FLOAT)) {
    $error = true;
}

$controller = new controller();
$addReview = $controller->addReview($profile_code, $review_text, $rating);
if ($addReview) {
    echo json_encode(['success' => true, 'message' => 'Review added correctly']);
} else {
    echo json_encode(['success' => false, 'error' => 'Error adding the review']);
}
?>