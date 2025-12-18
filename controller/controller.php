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
}
?>