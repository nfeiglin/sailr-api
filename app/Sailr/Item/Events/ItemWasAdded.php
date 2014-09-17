<?php namespace Sailr\Item\Events;

use Item;

class ItemWasAdded {
    public $item;

    function __construct(Item $item)
    {
        $this->item = $item;
    }

} 