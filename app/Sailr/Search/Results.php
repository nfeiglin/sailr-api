<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 18/09/2014
 * Time: 9:16 PM
 */

namespace Sailr\Search;


use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Support\Contracts\JsonableInterface;
use JsonSerializable;
use ArrayAccess;

/**
 * Class Results
 * @package Sailr\Search
 */
class Results implements JsonableInterface, JsonSerializable, ArrayableInterface, ArrayAccess {

    /**
     * @var array
     */
    public $items;
    /**
     * @var array
     */
    public $users;

    /**
     * @param $items
     * @param $users
     */
    public function __construct($items, $users)
    {
        $this->items = $items;
        $this->users = $users;
    }

    /**
     * @return array
     */
    public function toArray() {
        return [
            'items' => $this->items,
            'users' => $this->users
        ];
    }

    /**
     * @param int $options
     * @return string
     */
    public function toJson($options = 0) {
        return json_encode($this->toArray(), $options);
    }

    public function JsonSerialize() {
        return $this->toArray();
    }

    public function offsetExists($offset) {
        return array_key_exists($offset, $this->toArray());
    }

    public function offsetGet($offset) {
        return $this->toArray()[$offset];
    }

    public function offsetSet($offset, $value) {
        switch ($offset) {
            case "items":
                $this->items = $value;
                break;
            case "users":
                $this->users = $value;
                break;

        }
    }

    public function offsetUnset($offset) {
        switch ($offset) {
            case "items":
                $this->items = null;
                break;
            case "users":
                $this->users = null;
                break;

        }
    }



} 