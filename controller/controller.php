<?php
require_once '../Config/Database.php';
require_once '../model/UserModel.php';
require_once '../model/ReviewModel.php';
require_once '../model/VideogameModel.php';


class controller
{
    private $UserModel;
    private $VideogameModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();
        $this->UserModel = new UserModel($db);
        $this->VideogameModel = new VideogameModel($db);
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

    public function add_videogame($price, $name_, $plataform, $genre, $pegi, $stock, $companyname, $release_date)
    {
        return $this->VideogameModel->add_videogame($price, $name_, $plataform, $genre, $pegi, $stock, $companyname, $release_date);
    }
  
    public function create_review($profile_code, $review_text, $rating)
    {
        return $this->UserModel->create_review($profile_code, $review_text, $rating);
    }

    public function get_all_reviews()
    {
        return $this->UserModel->get_all_reviews();
    }
    
    public function delete_review($id)
    {
        return $this->UserModel->delete_review($id);
    }

    public function buy_ticket($profile_code, $event_id, $quantity)
    {
        return $this->UserModel->buy_ticket($profile_code, $event_id, $quantity);
    }

    public function get_user_tickets($profile_code)
    {
        return $this->UserModel->get_user_tickets($profile_code);
    }

    public function delete_item_from_cart($ticket_id)
    {
        return $this->UserModel->delete_item_from_cart($ticket_id);
    }
}
?>