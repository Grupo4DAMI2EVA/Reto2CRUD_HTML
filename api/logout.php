<?php
header("Content-Type: application/json");

session_start();
session_destroy();

// Limpiar cookie 
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

echo json_encode(["ok" => true, "mensaje" => "Sesión cerrada correctamente"], JSON_UNESCAPED_UNICODE);
?>
