<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 11/09/2014
 * Time: 6:34 PM
 */

namespace Sailr\ApiFeed;


interface FeedItemActionInterface {
    public function getCode();
    public function getTime();
    public function getTitle();
} 