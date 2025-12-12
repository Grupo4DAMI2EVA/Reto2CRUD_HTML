<?php
require_once 'Order.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

class OrderModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }
    public function get_all_orders()
    {
        $query = "SELECT * FROM ORDER_";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function add_order($quantity, $user_code, $videogame_code)
    {
        $query = "INSERT INTO ORDER_ (QUANTITY, USER_CODE, VIDEOGAME_CODE) VALUES (?, ?, ?)";
    }
    public function delete_order($order_code)
    {
        $query = "DELETE FROM ORDER_ WHERE ORDER_CODE = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $order_code);
        $stmt->execute();
        return $stmt->rowCount();
    }
}