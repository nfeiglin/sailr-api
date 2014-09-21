<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 21/09/2014
 * Time: 12:05 AM
 */
use Laracasts\Commander\CommanderTrait;
use Sailr\Feeds\GetUserFeedCommand;
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
} 