<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 1/09/2014
 * Time: 10:22 PM
 */

namespace Sailr\ApiFeed;


use Illuminate\Support\Contracts\ArrayableInterface;

class FeedItemActor implements FeedItemActorInterface, ArrayableInterface {

    /**
     * @var $title string The title used to actor (i.e Nathan Feiglin)
     * @var $objectType string A non-plural string of the type of object that the actor is (i.e user or item)
     */

    public $title;
    public $objectType;
    public $actorObject;

    public function __construct($title, $objectType, $actorObject){
        $this->title = $title;
        $this->objectType = $objectType;
        $this->actorObject = $actorObject;
    }

    /**
     * @return mixed
     */
    public function getActorObject()
    {
        return $this->actorObject;
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    public function toArray() {
        $meta =  [
            'title' => $this->getTitle(),
            'object' => $this->getObjectType()
        ];

        $returnArray = $meta + $this->actorObject;

        return $returnArray;
    }
} 