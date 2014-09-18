<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 18/09/2014
 * Time: 3:37 PM
 */

namespace Sailr\Item\Events;

use Item;
class ItemWasViewed {

    /**
     * @var $item Item
     */

    public $item;

    function __construct(Item $item)
    {
        $this->item = $item;
    }
}