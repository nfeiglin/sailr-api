<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 11/09/2014
 * Time: 5:59 PM
 */

namespace Sailr\ApiFeed;


interface FeedItemInterface {

    /**
     * @return FeedItemAction
     */
    public function getAction();

    /**
     * @return FeedItemActor
     */
    public function getActor();

    /**
     * @return FeedItemObject
     */
    public function getObject();

    /**
     * @return array
     */

    public function toArray();

    /**
     * @param $options mixed
     * @return string
     */

    public function toJson($options = 0);

} 