<?php
require_once 'Review.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

class ReviewModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function get_all_reviews()
    {
        $query = "SELECT * FROM REVIEW_";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_user_reviews($user_code) {
    $query = "SELECT * FROM REVIEW_ WHERE PROFILE_CODE = :user_code";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':user_code', $user_code, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function add_review($comment, $rating, $user_code, $videogame_code)
    {
        $query = "INSERT INTO REVIEW_ (COMMENT, RATING, PROFILE_CODE, VIDEOGAME_CODE) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $comment);
        $stmt->bindParam(2, $rating);
        $stmt->bindParam(3, $user_code);
        $stmt->bindParam(4, $videogame_code);
        $stmt->execute();
        return $stmt->rowCount();

    }
    public function delete_review($review_code)
    {
        $query = "DELETE FROM REVIEW_ WHERE REVIEW_CODE = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $review_code);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
?>