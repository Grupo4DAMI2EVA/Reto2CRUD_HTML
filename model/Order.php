<?php
class Order
{
    private $order_code;
    private $quantity;
    private $user_code;
    private $videogame_code;

    public function __construct($order_code, $quantity, $user_code, $videogame_code)
    {
        $this->order_code = $order_code;
        $this->quantity = $quantity;
        $this->user_code = $user_code;
        $this->videogame_code = $videogame_code;
    }
    public function getOrder_code()
    {
        return $this->order_code;
    }
    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }
}
?>