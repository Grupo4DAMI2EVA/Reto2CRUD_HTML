<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

header('Content-Type: application/json; charset=utf-8');

require_once '../controller/controller.php';

$profile_code = $_GET['profile_code'] ?? '';
$review_id = $_GET['review_id'] ?? '';

$controller = new controller();
$deleteReview = $controller->delete_review($review_id);

if ($deleteReview) {
    echo json_encode(['success' => true, 'message' => 'Review deleted correctly']);
} else {
    echo json_encode(['success' => false, 'error' => 'Error deleting the review']);
}
?>