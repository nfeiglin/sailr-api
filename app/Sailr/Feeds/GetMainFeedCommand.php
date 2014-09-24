<?php namespace Sailr\Feeds;

class GetMainFeedCommand {

    /**
     * @var string
     */
    public $loggedInUserId;

    /**
     * @param string loggedInUserId
     */
    public function __construct($loggedInUserId)
    {
        $this->loggedInUserId = $loggedInUserId;
    }

}