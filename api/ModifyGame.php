<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

header('Content-Type: application/json; charset=utf-8');

require_once '../controller/controller.php';

// Validation functions
function validateName($name) {
    $name = trim($name);
    if (empty($name)) {
        return ['valid' => false, 'message' => 'Name is required'];
    }
    if (strlen($name) < 3) {
        return ['valid' => false, 'message' => 'Name must be at least 3 characters'];
    }
    if (strlen($name) > 100) {
        return ['valid' => false, 'message' => 'Name cannot exceed 100 characters'];
    }
    if (!preg_match('/^[a-zA-Z0-9\s\-\:\.]+$/', $name)) {
        return ['valid' => false, 'message' => 'Name can only contain letters, numbers, hyphens, colons, dots and spaces'];
    }
    return ['valid' => true];
}

function validatePlatform($platform) {
    $validPlatforms = ['pc', 'playstation', 'xbox', 'nintendo', 'other'];
    if (empty($platform)) {
        return ['valid' => false, 'message' => 'Platform is required'];
    }
    if (!in_array($platform, $validPlatforms)) {
        return ['valid' => false, 'message' => 'Invalid platform selected'];
    }
    return ['valid' => true];
}

function validateCompany($company) {
    $company = trim($company);
    if (empty($company)) {
        return ['valid' => false, 'message' => 'Company is required'];
    }
    if (strlen($company) < 2) {
        return ['valid' => false, 'message' => 'Company must be at least 2 characters'];
    }
    if (strlen($company) > 50) {
        return ['valid' => false, 'message' => 'Company cannot exceed 50 characters'];
    }
    if (!preg_match('/^[a-zA-Z\s\-\.]+$/', $company)) {
        return ['valid' => false, 'message' => 'Company can only contain letters, hyphens, dots and spaces'];
    }
    return ['valid' => true];
}

function validateStock($stock) {
    if (empty($stock)) {
        return ['valid' => false, 'message' => 'Stock is required'];
    }
    if (!filter_var($stock, FILTER_VALIDATE_INT) && $stock !== '0') {
        return ['valid' => false, 'message' => 'Stock must be a valid integer'];
    }
    $stockNum = (int)$stock;
    if ($stockNum < 0) {
        return ['valid' => false, 'message' => 'Stock cannot be negative'];
    }
    if ($stockNum > 999999) {
        return ['valid' => false, 'message' => 'Stock is too large'];
    }
    return ['valid' => true];
}

function validateGenre($genre) {
    $validGenres = ['action', 'adventure', 'rpg', 'platformer', 'shooter', 'strategy', 'racing', 'sports', 'simulation', 'educational', 'other'];
    if (empty($genre)) {
        return ['valid' => false, 'message' => 'Genre is required'];
    }
    if (!in_array($genre, $validGenres)) {
        return ['valid' => false, 'message' => 'Invalid genre selected'];
    }
    return ['valid' => true];
}

function validatePrice($price) {
    if (empty($price)) {
        return ['valid' => false, 'message' => 'Price is required'];
    }
    if (!filter_var($price, FILTER_VALIDATE_FLOAT)) {
        return ['valid' => false, 'message' => 'Price must be a valid number'];
    }
    $priceNum = (float)$price;
    if ($priceNum <= 0) {
        return ['valid' => false, 'message' => 'Price must be greater than 0'];
    }
    if ($priceNum > 99999.99) {
        return ['valid' => false, 'message' => 'Price is too large'];
    }
    if (!preg_match('/^\d+(\.\d{1,2})?$/', $price)) {
        return ['valid' => false, 'message' => 'Price can have maximum 2 decimal places'];
    }
    return ['valid' => true];
}

function validatePegi($pegi) {
    $validPegis = ['3', '7', '12', '16', '18'];
    if (empty($pegi)) {
        return ['valid' => false, 'message' => 'PEGI is required'];
    }
    if (!in_array($pegi, $validPegis)) {
        return ['valid' => false, 'message' => 'Invalid PEGI selected'];
    }
    return ['valid' => true];
}

function validateReleaseDate($date) {
    if (empty($date)) {
        return ['valid' => false, 'message' => 'Release Date is required'];
    }
    
    $dateObj = DateTime::createFromFormat('Y-m-d', $date);
    
    if (!$dateObj) {
        return ['valid' => false, 'message' => 'Invalid date format'];
    }
    
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    
    if ($dateObj > $today) {
        return ['valid' => false, 'message' => 'Release date cannot be in the future'];
    }
    
    return ['valid' => true];
}

try {
    // Get POST data
    $code = filter_input(INPUT_POST, "code", FILTER_UNSAFE_RAW);
    $name = filter_input(INPUT_POST, "name", FILTER_UNSAFE_RAW);
    $platform = filter_input(INPUT_POST, "platform", FILTER_UNSAFE_RAW);
    $company = filter_input(INPUT_POST, "company", FILTER_UNSAFE_RAW);
    $stock = filter_input(INPUT_POST, "stock", FILTER_UNSAFE_RAW);
    $genre = filter_input(INPUT_POST, "genre", FILTER_UNSAFE_RAW);
    $price = filter_input(INPUT_POST, "price", FILTER_UNSAFE_RAW);
    $pegi = filter_input(INPUT_POST, "pegi", FILTER_UNSAFE_RAW);
    $releaseDate = filter_input(INPUT_POST, "releaseDate", FILTER_UNSAFE_RAW);

    // Validate code
    if (empty($code) || !filter_var($code, FILTER_VALIDATE_INT)) {
        http_response_code(400);
        echo json_encode([
            'result' => 'Invalid game code',
            'status' => 400,
            'success' => false
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Validate each field
    $nameValidation = validateName($name);
    if (!$nameValidation['valid']) {
        http_response_code(400);
        echo json_encode([
            'result' => $nameValidation['message'],
            'status' => 400,
            'success' => false
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $platformValidation = validatePlatform($platform);
    if (!$platformValidation['valid']) {
        http_response_code(400);
        echo json_encode([
            'result' => $platformValidation['message'],
            'status' => 400,
            'success' => false
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $companyValidation = validateCompany($company);
    if (!$companyValidation['valid']) {
        http_response_code(400);
        echo json_encode([
            'result' => $companyValidation['message'],
            'status' => 400,
            'success' => false
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stockValidation = validateStock($stock);
    if (!$stockValidation['valid']) {
        http_response_code(400);
        echo json_encode([
            'result' => $stockValidation['message'],
            'status' => 400,
            'success' => false
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $genreValidation = validateGenre($genre);
    if (!$genreValidation['valid']) {
        http_response_code(400);
        echo json_encode([
            'result' => $genreValidation['message'],
            'status' => 400,
            'success' => false
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $priceValidation = validatePrice($price);
    if (!$priceValidation['valid']) {
        http_response_code(400);
        echo json_encode([
            'result' => $priceValidation['message'],
            'status' => 400,
            'success' => false
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $pegiValidation = validatePegi($pegi);
    if (!$pegiValidation['valid']) {
        http_response_code(400);
        echo json_encode([
            'result' => $pegiValidation['message'],
            'status' => 400,
            'success' => false
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $releaseDateValidation = validateReleaseDate($releaseDate);
    if (!$releaseDateValidation['valid']) {
        http_response_code(400);
        echo json_encode([
            'result' => $releaseDateValidation['message'],
            'status' => 400,
            'success' => false
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // All validations passed, modify the videogame
    $controller = new controller();
    $modify = $controller->modify_videogame(
        $code,
        $price,
        $name,
        $platform,
        $genre,
        $pegi,
        $stock,
        $company,
        $releaseDate
    );

    if ($modify) {
        http_response_code(200);
        echo json_encode([
            'result' => 'El videojuego ha sido modificado correctamente.',
            'status' => 200,
            'success' => true
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(400);
        echo json_encode([
            'result' => 'No se ha modificado correctamente el videojuego.',
            'status' => 400,
            'success' => false
        ], JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Error del servidor: ' . $e->getMessage(),
        'status' => 500,
        'success' => false
    ], JSON_UNESCAPED_UNICODE);
}
?>