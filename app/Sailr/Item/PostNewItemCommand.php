<?php namespace Sailr\Item;

class PostNewItemCommand {

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var string
     */
    public $price;

    /**
     * @var string
     */
    public $user_id;

    /**
     * @param string title
     * @param string currency
     * @param string price
     * @param string user_id
     */
    public function __construct($title, $currency, $price, $user_id)
    {
        $this->title = $title;
        $this->currency = $currency;
        $this->price = $price;
        $this->user_id = $user_id;
    }

}