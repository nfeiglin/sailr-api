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

class FeedItem implements ArrayableInterface, JsonableInterface {

    /**
     * @var $action FeedItemAction
     * @var $actor FeedItemAction
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

    public function toArray() {
        return [
            'action' => $this->action->toArray(),
            'actor' => $this->actor->toArray(),
            'object' => $this->object->toArray()
        ];
    }

    public function toJson($options = 0) {
        return json_encode($this->toArray(), $options);
    }
} 