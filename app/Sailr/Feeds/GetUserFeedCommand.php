<?php namespace Sailr\Feeds;

class GetUserFeedCommand {

    /**
     * @var string
     */
    public $user_id;

    /**
     * @param string user_id
     */
    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

}