<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 1/09/2014
 * Time: 10:22 PM
 */

namespace Sailr\ApiFeed;


use Illuminate\Support\Collection;
use Illuminate\Support\Contracts\ArrayableInterface;

class FeedItemObject implements ArrayableInterface {

    /**
     * @var $objectType string A non-plural string of the type of object that the actor is (i.e user or item)
     * @var $objectItem \stdClass A model object for this feed item
     */
    protected $objectType;
    protected $objectItem;

    public function __construct($objectType, $objectItem) {
        $this->objectType = $objectType;
        $this->objectItem = $objectItem;
    }

    public function toArray() {

        if ($this->objectItem instanceof Collection) {
            $this->objectItem->toArray();
        }

        $returnArray = ['object' => $this->objectType] + $this->objectItem;

        return $returnArray;
    }

}