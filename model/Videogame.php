<?php
class Videogame
{
    private $videogame_code;
    private $price;
    private $name_;
    private $plataform;
    private $genre;
    private $pegi;
    private $stock;
    private $companyname;
    private $release_date;

    public function __construct($videogame_code, $price, $name_, $plataform, $genre, $pegi, $stock, $companyname, $release_date)
    {
        $this->videogame_code = $videogame_code;
        $this->price = $price;
        $this->name_ = $name_;
        $this->plataform = $plataform;
        $this->genre = $genre;
        $this->pegi = $pegi;
        $this->stock = $stock;
        $this->companyname = $companyname;
        $this->release_date = $release_date;
    }
    public function getVideogame_code()
    {
        return $this->videogame_code;
    }
    public function getPrice()
    {
        return $this->price;
    }
    public function getName_()
    {
        return $this->name_;
    }
    public function getPlataform()
    {
        return $this->plataform;
    }
    public function getGenre()
    {
        return $this->genre;
    }
    public function getPegi()
    {
        return $this->pegi;
    }
    public function getStock()
    {
        return $this->stock;
    }
    public function getCompanyname()
    {
        return $this->companyname;
    }
    public function getRelease_date()
    {
        return $this->release_date;
    }
    public function setVideogame_code($videogame_code)
    {
        $this->videogame_code = $videogame_code;
    }
    public function setPrice($price)
    {
        $this->price = $price;
    }
    public function setName_($name_)
    {
        $this->name_ = $name_;
    }
    public function setPlataform($plataform)
    {
        $this->plataform = $plataform;
    }
    public function setGenre($genre)
    {
        $this->genre = $genre;
    }
    public function setPegi($pegi)
    {
        $this->pegi = $pegi;
    }
    public function setStock($stock)
    {
        $this->stock = $stock;
    }
    public function setCompanyname($companyname)
    {
        $this->companyname = $companyname;
    }
    public function setRelease_date($release_date)
    {
        $this->release_date = $release_date;
    }
}
?>