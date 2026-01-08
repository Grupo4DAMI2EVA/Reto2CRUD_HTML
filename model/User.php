<?php

require_once 'Profile.php';

class User extends Profile
{
    private $gender;
    private $cardNumber;

    private $balance;

    public function __construct($profile_code, $email, $user_name, $pswd, $telephone, $name_, $surname, $gender, $cardnumber, $balance)
    {
        parent::__construct(
            $profile_code,
            $email,
            $user_name,
            $pswd,
            $telephone,
            $name_,
            $surname
        );
        $this->gender = $gender;
        $this->cardNumber = $cardnumber;
        $this->balance = $balance;
    }
    public function getGender()
    {
        return $this->gender;
    }
    public function setGender($gender)
    {
        $this->gender = $gender;
    }
    public function getCardNumber()
    {
        return $this->cardNumber;
    }
    public function setCardNumber($cardNumber)
    {
        $this->cardNumber = $cardNumber;
    }
    public function getBalance()
    {
        return $this->balance;
    }

    public function setBalance($balance)
    {
        $this->balance = $balance;
    }
    public function __toString()
    {
        return "User: " . parent::mostrar() . " - Gender: " . $this->gender . " - Card Number: " . $this->cardNumber;
    }
}

?>