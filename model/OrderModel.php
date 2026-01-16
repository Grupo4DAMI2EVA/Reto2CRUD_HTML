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
    public function add_order($quantity, $profile_code, $videogame_code)
    {
        $query = "INSERT INTO ORDER_ (QUANTITY, PROFILE_CODE, VIDEOGAME_CODE) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $quantity);
        $stmt->bindParam(2, $profile_code);
        $stmt->bindParam(3, $videogame_code);
        
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    public function delete_order($order_code)
    {
        $query = "DELETE FROM ORDER_ WHERE ORDER_CODE = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $order_code);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function validateCart($cartItems)
    {
        if (empty($cartItems) || !is_array($cartItems)) {
            return ['valid' => false, 'error_type' => 'empty_cart', 'message' => 'El carrito está vacío'];
        }

        foreach ($cartItems as $item) {
            $videogame_code = intval($item['id'] ?? $item['videogame_code'] ?? 0);
            $quantity = intval($item['qty'] ?? $item['quantity'] ?? 0);

            if ($videogame_code <= 0 || $quantity <= 0) {
                return ['valid' => false, 'error_type' => 'invalid_item', 'message' => 'Hay items inválidos en el carrito'];
            }
        }

        return ['valid' => true];
    }

    public function processPurchase($profile_code, $cartItems, $videogameModel, $userModel)
    {
        // 1. Validar carrito (OrderModel)
        $cartValidation = $this->validateCart($cartItems);
        if (!$cartValidation['valid']) {
            return ['success' => false, 'error_type' => $cartValidation['error_type'], 'message' => $cartValidation['message']];
        }

        // 2. Validar stock (VideogameModel)
        $stockValidation = $videogameModel->validateStock($cartItems);
        if (!$stockValidation['valid']) {
            return ['success' => false, 'error_type' => $stockValidation['error_type'], 'message' => $stockValidation['message']];
        }

        $validatedItems = $stockValidation['items'];
        $videogamesMap = $stockValidation['videogamesMap'];

        // 3. Calcular costo total (VideogameModel)
        $totalCost = $videogameModel->calculateTotalCost($validatedItems);

        // 4. Validar saldo (UserModel)
        $balanceValidation = $userModel->validateBalance($profile_code, $totalCost);
        if (!$balanceValidation['valid']) {
            return [
                'success' => false,
                'error_type' => $balanceValidation['error_type'],
                'message' => $balanceValidation['message'],
                'balance' => $balanceValidation['balance'] ?? null,
                'required' => $balanceValidation['required'] ?? null,
                'needed' => $balanceValidation['needed'] ?? null
            ];
        }

        // 5. Crear órdenes (OrderModel)
        $orders = [];
        foreach ($validatedItems as $item) {
            if (!$this->add_order($item['quantity'], $profile_code, $item['videogame_code'])) {
                return ['success' => false, 'error_type' => 'order_error', 'message' => 'Error al crear la orden'];
            }
            $orders[] = [
                'videogame_code' => $item['videogame_code'],
                'quantity' => $item['quantity']
            ];
        }

        // 6. Reducir stock (VideogameModel)
        if (!$videogameModel->processStockReduction($orders, $videogamesMap)) {
            return ['success' => false, 'error_type' => 'stock_update_error', 'message' => 'Error al actualizar el stock'];
        }

        // 7. Descontar saldo (UserModel)
        if (!$userModel->deductBalance($profile_code, $totalCost)) {
            return ['success' => false, 'error_type' => 'balance_update_error', 'message' => 'Error al actualizar el saldo'];
        }

        return ['success' => true, 'message' => 'Compra realizada con éxito'];
    }
}