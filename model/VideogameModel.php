<?php
require_once 'Videogame.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

class VideogameModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function get_all_videogames()
    {
        $query = "SELECT * FROM VIDEOGAME_";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add_videogame($price, $name_, $plataform, $genre, $pegi, $stock, $companyname, $release_date)
    {
        $query = "INSERT INTO VIDEOGAME_ (PRICE, NAME_, PLATAFORM, GENRE, PEGI, STOCK, COMPANYNAME, RELEASE_DATE) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $price);
        $stmt->bindParam(2, $name_);
        $stmt->bindParam(3, $plataform);
        $stmt->bindParam(4, $genre);
        $stmt->bindParam(5, $pegi);
        $stmt->bindParam(6, $stock);
        $stmt->bindParam(7, $companyname);
        $stmt->bindParam(8, $release_date);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function modify_videogame($videogame_code, $price, $name_, $plataform, $genre, $pegi, $stock, $companyname, $release_date)
    {
        $query = "UPDATE VIDEOGAME_ SET PRICE = ?, NAME_ = ?, PLATAFORM = ?, GENRE = ?, PEGI = ?, STOCK = ?, COMPANYNAME = ?, RELEASE_DATE = ? WHERE VIDEOGAME_CODE = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $price);
        $stmt->bindParam(2, $name_);
        $stmt->bindParam(3, $plataform);
        $stmt->bindParam(4, $genre);
        $stmt->bindParam(5, $pegi);
        $stmt->bindParam(6, $stock);
        $stmt->bindParam(7, $companyname);
        $stmt->bindParam(8, $release_date);
        $stmt->bindParam(9, $videogame_code);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function delete_videogame($videogame_code)
    {
        $query = "DELETE FROM VIDEOGAME_ WHERE VIDEOGAME_CODE = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $videogame_code);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function updateStock($videogame_code, $newStock)
    {
        $query = "UPDATE VIDEOGAME_ SET STOCK = ? WHERE VIDEOGAME_CODE = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $newStock);
        $stmt->bindParam(2, $videogame_code);
        return $stmt->execute();
    }

    public function validateStock($cartItems)
    {
        $allVideogames = $this->get_all_videogames();
        $videogamesMap = [];
        foreach ($allVideogames as $game) {
            $videogamesMap[$game['VIDEOGAME_CODE']] = $game;
        }

        $stockErrors = [];
        $validatedItems = [];

        foreach ($cartItems as $item) {
            $videogame_code = intval($item['id'] ?? $item['videogame_code'] ?? 0);
            $quantity = intval($item['qty'] ?? $item['quantity'] ?? 0);

            if (!isset($videogamesMap[$videogame_code])) {
                return [
                    'valid' => false,
                    'error_type' => 'not_found',
                    'message' => 'Uno o más videojuegos no se encontraron'
                ];
            }

            $game = $videogamesMap[$videogame_code];

            if ($game['STOCK'] < $quantity) {
                $stockErrors[] = [
                    'name' => $game['NAME_'],
                    'requested' => $quantity,
                    'available' => $game['STOCK']
                ];
            }

            $validatedItems[] = [
                'videogame_code' => $videogame_code,
                'quantity' => $quantity,
                'price' => floatval($game['PRICE']),
                'current_stock' => $game['STOCK'],
                'name' => $game['NAME_']
            ];
        }

        if (!empty($stockErrors)) {
            $errorMessages = [];
            foreach ($stockErrors as $error) {
                $errorMessages[] = "{$error['name']}: solicitas {$error['requested']} pero solo hay {$error['available']} disponibles";
            }
            return [
                'valid' => false,
                'error_type' => 'insufficient_stock',
                'message' => 'Stock insuficiente: ' . implode('. ', $errorMessages)
            ];
        }

        return [
            'valid' => true,
            'items' => $validatedItems,
            'videogamesMap' => $videogamesMap
        ];
    }

    public function calculateTotalCost($validatedItems)
    {
        $totalCost = 0;
        foreach ($validatedItems as $item) {
            $totalCost += $item['price'] * $item['quantity'];
        }
        return $totalCost;
    }

    public function processStockReduction($orders, $videogamesMap)
    {
        foreach ($orders as $order) {
            $newStock = $videogamesMap[$order['videogame_code']]['STOCK'] - $order['quantity'];
            if (!$this->updateStock($order['videogame_code'], $newStock)) {
                return false;
            }
        }
        return true;
    }
}
?>