<?php
class Review
{
    private $review_code;
    private $comment;
    private $rating;
    private $user_code;
    private $videogame_code;

    public function __construct($review_code, $comment, $rating, $user_code, $videogame_code)
    {
        $this->review_code = $review_code;
        $this->comment = $comment;
        $this->rating = $rating;
        $this->user_code = $user_code;
        $this->videogame_code = $videogame_code;
    }
    public function getReview_code()
    {
        return $this->review_code;
    }
    public function getComment()
    {
        return $this->comment;
    }
    public function getRating()
    {
        return $this->rating;
    }
    public function setReview_code($review_code)
    {
        $this->review_code = $review_code;
    }
    public function setComment($comment)
    {
        $this->comment = $comment;
    }
    public function setRating($rating)
    {
        $this->rating = $rating;
    }
}
?>