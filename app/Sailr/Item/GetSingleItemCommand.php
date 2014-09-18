<?php namespace Sailr\Item;

class GetSingleItemCommand {

    /**
     * @var string
     */
    public $id;

    /**
     * @param string id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

}