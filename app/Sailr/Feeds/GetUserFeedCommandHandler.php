<?php namespace Sailr\Feeds;

use Laracasts\Commander\CommandHandler;
use Sailr\Item\ItemRepository;
use Sailr\Api\Responses\Responder;
use RelationshipHelpers;

class GetUserFeedCommandHandler implements CommandHandler {

    /**
     * @var ItemRepository
     */
    protected $itemsRepository;

    /**
     * @var Responder
     */
    protected $responder;

    public function __construct(ItemRepository $itemsRepository, Responder $responder) {
        $this->itemsRepository = $itemsRepository;
        $this->responder = $responder;
    }
    /**
     * Handle the command.
     *
     * @param object $command
     * @return void
     */
    public function handle($command)
    {
        $user_id = $command->user_id;

        $items = $user->items()->where('public', '=', 1)->with(['User', 'Photos' => function($y) {
            $y->where('type', '=', 'full_res');
            $y->select(['url', 'id', 'item_id']);
        }]);

        $feedCollectionBuilder = new \Sailr\ApiFeed\FeedCollectionBuilder;
        $feedCollectionBuilder->createItemsFeed($items->get());

        return Response::json($feedCollectionBuilder->getFeedCollection());

        //return Response::json($items->get());


        $paginator = $items->paginate($resultsPerPage);
        $items = $items->get();
        $items = $items->toArray();




        $isSelf = false;
        $follow_you = false;
        $you_follow = false;
        if (Auth::check()) {
            if (Auth::user()->username == $username) {
                $isSelf = true;
            }

            $follow_you = RelationshipHelpers::follows_you($user);
            $you_follow = RelationshipHelpers::you_follow($user);
        }


        $no_of_followers = RelationshipHelpers::count_follows_user($user);
        $no_of_following = RelationshipHelpers::count_user_following($user);

        $mutual = false;

        if ($follow_you && $you_follow) {
            $mutual = true;
        }

        $userArray = $user->toArray();

        /*
        return View::make('users.show')
            ->with('title', $user['username'])
            ->with('user', $userArray)
            ->with('items', $items)
            ->with('paginator', $paginator)
            ->with('follows_you', $follow_you)
            ->with('you_follow', $you_follow)
            ->with('mutual', $mutual)
            ->with('is_self', $isSelf)
            ->with('no_of_followers', $no_of_followers)
            ->with('no_of_following', $no_of_following)
            ;
        */


    }

}