<?php
require_once '../Config/Database.php';
require_once '../model/UserModel.php';
require_once '../model/ReviewModel.php';
require_once '../model/VideogameModel.php';
require_once '../model/OrderModel.php';


class controller
{
    private $UserModel;
    private $VideogameModel;

    private $ReviewModel;
    private $OrderModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();
        $this->UserModel = new UserModel($db);
        $this->VideogameModel = new VideogameModel($db);
        $this->ReviewModel = new ReviewModel($db);
        $this->OrderModel = new OrderModel($db);
    }

    public function loginUser($username, $password)
    {
        return $this->UserModel->loginUser($username, $password);
    }

    public function loginAdmin($username, $password)
    {
        return $this->UserModel->loginAdmin($username, $password);
    }

    public function checkUser($username, $password)
    {
        return $this->UserModel->checkUser($username, $password);
    }

    public function create_user($username, $pswd1)
    {
        return $this->UserModel->create_user($username, $pswd1);
    }

    public function get_all_users()
    {
        return $this->UserModel->get_all_users();
    }

    public function modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code)
    {
        return $this->UserModel->modifyUser($email, $username, $telephone, $name, $surname, $gender, $card_no, $profile_code);
    }

    public function modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code)
    {
        return $this->UserModel->modifyAdmin($email, $username, $telephone, $name, $surname, $current_account, $profile_code);
    }

    public function delete_user($id)
    {
        return $this->UserModel->delete_user($id);
    }

    public function modifyPassword($profile_code, $password)
    {
        return $this->UserModel->modifyPassword($profile_code, $password);
    }

    public function addBalance($profile_code, $amount)
    {
        return $this->UserModel->addBalance($profile_code, $amount);
    }

    //VideogameModel
    public function get_all_videogames()
    {
        return $this->VideogameModel->get_all_videogames();
    }

    public function add_videogame($price, $name_, $plataform, $genre, $pegi, $stock, $companyname, $release_date)
    {
        return $this->VideogameModel->add_videogame($price, $name_, $plataform, $genre, $pegi, $stock, $companyname, $release_date);
    }

    public function modify_videogame($videogame_code, $price, $name_, $plataform, $genre, $pegi, $stock, $companyname, $release_date)
    {
        return $this->VideogameModel->modify_videogame($videogame_code, $price, $name_, $plataform, $genre, $pegi, $stock, $companyname, $release_date);
    }

    public function delete_videogame($videogame_code)
    {
        return $this->VideogameModel->delete_videogame($videogame_code);
    }

    public function add_review($comment, $rating, $user_code, $videogame_code)
    {
        return $this->ReviewModel->add_review($comment, $rating, $user_code, $videogame_code);
    }

    public function get_all_reviews()
    {
        return $this->ReviewModel->get_all_reviews();
    }

    public function delete_review($id)
    {
        return $this->ReviewModel->delete_review($id);
    }

    public function buy_ticket($profile_code, $event_id, $quantity)
    {
        return $this->ReviewModel->buy_ticket($profile_code, $event_id, $quantity);
    }

    public function get_user_tickets($profile_code)
    {
        return $this->ReviewModel->get_user_tickets($profile_code);
    }

    public function delete_item_from_cart($ticket_id)
    {
        return $this->ReviewModel->delete_item_from_cart($ticket_id);
    }

    public function processPurchase($profile_code, $cartItems)
    {
        return $this->OrderModel->processPurchase($profile_code, $cartItems, $this->VideogameModel, $this->UserModel);
    }

    public function get_user_by_profile_code($profile_code)
    {
        return $this->UserModel->get_user_by_profile_code($profile_code);
    }
}
?>