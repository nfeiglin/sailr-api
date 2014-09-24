<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 21/09/2014
 * Time: 12:05 AM
 */
use Laracasts\Commander\CommanderTrait;
use Sailr\Feeds\GetUserFeedCommand;
use Sailr\Feeds\GetMainFeedCommand;
use Laracasts\Commander\CommandBus;

class FeedsController extends BaseController {
    use CommanderTrait;

    protected $commandBus;

    public function __construct(CommandBus $commandBus) {
        $this->commandBus = $commandBus;
    }

    public function show($user_id) {
        $feed = new GetUserFeedCommand($user_id);
        return $this->commandBus->execute($feed);
    }

    /*
     * Return the user's main timeline with all the items that were added by people they follow in reverse chronological order
     */
    public function userFeed() {
        //$timeline = new GetMainFeedCommand(Auth::user()->getAuthIdentifier());
        $timeline = new GetMainFeedCommand(12);
        return $this->commandBus->execute($timeline);
    }
} 