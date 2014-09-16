<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 11/09/2014
 * Time: 6:28 PM
 */

namespace Sailr\ApiFeed;


interface FeedItemActorInterface {
    /**
     * @return mixed
     */
    public function getActorObject();

    /**
     * @return string
     */
    public function getObjectType();

    /**
     * @return string
     */
    public function getTitle();

    public function toArray();
} 