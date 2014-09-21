<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 21/09/2014
 * Time: 5:16 PM
 */

namespace Sailr\Users\DTO;


use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Support\Contracts\JsonableInterface;
use JsonSerializable;

class BasicRelationship implements ArrayableInterface, JsonableInterface, JsonSerializable {

    /**
     * @var bool
     */
    public $follows_you;

    /**
     * @var bool
     */
    public $you_follow;

    /**
     * @var int
     */
    public $follower_count;

    /**
     * @var int
     */
    public $following_count;

    function __construct($follower_count, $following_count, $follows_you = null, $you_follow = null)
    {
        $this->follower_count = $follower_count;
        $this->following_count = $following_count;
        $this->follows_you = $follows_you;
        $this->you_follow = $you_follow;
    }

    public function toArray() {

        $array = [];
        $array['counts'] = [
            'followers' => $this->follower_count,
            'following' => $this->following_count
        ];

        if ($this->follows_you !== null | ! $this->you_follow !== null) {
            $array = array_merge($array,[
                    'follows_you' => $this->follows_you,
                    'you_follow' => $this->you_follow,
                    'object' => 'relationship.basic'
                ]);
        }


        return $array;

    }

    public function toJson($options = 0) {
        return json_encode($this->toArray(), $options);
    }

    public function jsonSerialize() {
        return $this->toJson();
    }


} 