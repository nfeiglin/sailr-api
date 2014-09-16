<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 1/09/2014
 * Time: 10:20 PM
 */

namespace Sailr\ApiFeed;


use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Support\Contracts\JsonableInterface;

class FeedItem implements FeedItemInterface, ArrayableInterface, JsonableInterface {

    /**
     * @var $action FeedItemAction
     * @var $actor FeedItemActor
     * @var $object FeedItemObject
     */
    public $action;
    public $actor;
    public $object;

    public function __construct(FeedItemAction $action, FeedItemActor $actor, FeedItemObject $object) {
        $this->action = $action;
        $this->actor = $actor;
        $this->object = $object;
    }

    /**
     * @return FeedItemAction
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return FeedItemActor
     */
    public function getActor()
    {
        return $this->actor;
    }

    /**
     * @return FeedItemObject
     */
    public function getObject()
    {
        return $this->object;
    }

    public function toArray() {
        return [
            'action' => $this->getAction()->toArray(),
            'actor' => $this->getActor()->toArray(),
            'object' => $this->getObject()->toArray()
        ];
    }

    public function toJson($options = 0) {
        return json_encode($this->toArray(), $options);
    }
} 