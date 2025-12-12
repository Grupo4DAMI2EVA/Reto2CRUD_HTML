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

}
?>