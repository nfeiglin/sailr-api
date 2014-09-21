<?php namespace Sailr\Feeds;

use Laracasts\Commander\CommandHandler;
use Sailr\Item\ItemRepository;
use Sailr\Api\Responses\Responder;
use Sailr\ApiFeed\FeedCollectionBuilder;

class GetUserFeedCommandHandler implements CommandHandler {

    /**
     * @var ItemRepository
     */
    protected $itemsRepository;

    /**
     * @var Responder
     */
    protected $responder;

    /**
     * @var FeedCollectionBuilder
     */
    protected $feedBuilder;

    public function __construct(ItemRepository $itemsRepository, Responder $responder, FeedCollectionBuilder $feedCollectionBuilder) {
        $this->itemsRepository = $itemsRepository;
        $this->responder = $responder;
        $this->feedBuilder = $feedCollectionBuilder;
    }
    /**
     * Handle the command.
     *
     * @param object $command
     * @return Response
     */
    public function handle($command)
    {
        $user_id = $command->user_id;

        $paginatorAndItems = $this->itemsRepository->getAllItemsForUserPaginated($user_id);
        $items = $paginatorAndItems->getUnderlyingData();

        $this->feedBuilder->createItemsFeed($items);
        $feed = $this->feedBuilder->getFeedCollection();

        return $this->responder->paginatedResponse($paginatorAndItems, $feed);




    }

}