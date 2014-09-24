<?php namespace Sailr\Feeds;

use Laracasts\Commander\CommandHandler;
use Sailr\Item\ItemRepository;
use Sailr\Api\Responses\Responder;
use Sailr\ApiFeed\FeedCollectionBuilder;

class GetMainFeedCommandHandler implements CommandHandler {

    /**
     * @var ItemRepository
     */

    protected $itemRepository;

    /**
     * @var FeedCollectionBuilder
     */
    protected $feedBuilder;

    /**
     * @var Responder
     */
    protected $responder;

    public function __construct(ItemRepository $itemRepository, FeedCollectionBuilder $feedCollectionBuilder, Responder $responder)
    {
        $this->itemRepository = $itemRepository;
        $this->feedBuilder = $feedCollectionBuilder;
        $this->responder = $responder;
    }


    /**
     * Handle the command.
     *
     * @param object $command
     * @return Response
     */
    public function handle($command)
    {

        $user_id = $command->loggedInUserId;

        $following = \Relationship::where('user_id', $user_id)->get(array('follows_user_id'));
        $following = array_flatten($following->toArray());

        /*
         * The following line makes sure the the user's own posts show up in the feed!
         */

        $following[(count($following) + 1)] = $user_id;

        $paginatedItemResults = $this->itemRepository->getAllItemsForUserIdsPaginated($following);

        $items = $paginatedItemResults->getUnderlyingData();

        $this->feedBuilder->createItemsFeed($items);
        $feed = $this->feedBuilder->getFeedCollection();

        return $this->responder->paginatedResponse($paginatedItemResults, $feed);

    }

}