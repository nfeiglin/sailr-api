<?php


namespace Sailr\Tags;


class BaseTagger {

    /**
     * @var $taggedUsernames an array of the usernames of all the people tagged in a string
     */
    protected $taggedUsernames;

    public static function make() {
        return new static;
    }

    public function getTaggedUserNames($string) {

        $regexPattern = '/(?<=@)[^\s]+/';

        preg_match_all($regexPattern, $string, $matches);

        $this->taggedUsernames = $matches[0];

        return $this->taggedUsernames;

    }
}