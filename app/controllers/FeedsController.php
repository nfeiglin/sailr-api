<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 21/09/2014
 * Time: 12:05 AM
 */
use Laracasts\Commander\CommanderTrait;
use Sailr\Feeds\GetUserFeedCommand;

class FeedsController extends BaseController {
    use CommanderTrait;

    public function show($id) {
        return new GetUserFeedCommand($id);
    }
} 