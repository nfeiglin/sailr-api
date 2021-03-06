<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 1/09/2014
 * Time: 10:36 PM
 */

namespace Sailr\ApiFeed;


use Carbon\Carbon;
use Illuminate\Support\Collection;

class FeedCollectionBuilder {

    /**
     * @var $feedCollection FeedCollection The feed collection
     */
    public $feedCollection;

    public function __construct() {
        $this->feedCollection = new FeedCollection;
    }

    public function addObjectToFeedCollection(FeedItemInterface $feedItem) {
        $this->feedCollection->push($feedItem);
    }

    public function makeFeedItem(FeedItemAction $action, FeedItemActor $actor, FeedItemObject $object) {
        return new FeedItem($action, $actor, $object);
    }

    /**
     * @return FeedCollection
     */

    public function getFeedCollection() {
        return $this->feedCollection;
    }

    /**
     * @param FeedCollection $feedCollection
     */
    public function setFeedCollection(FeedCollection $feedCollection) {
        $this->feedCollection = $feedCollection;
    }

    public function createItemsFeed($modelCollection) {

        if ($modelCollection instanceof Collection) {
            $modelCollection = $modelCollection->toArray();
        }
        foreach($modelCollection as $model) {

            $dt = new \DateTime($model['created_at']);

            $carbon = Carbon::createFromTimestamp($dt->getTimestamp());

            $action = new FeedItemAction('item.create', 'added new item', $carbon);
            $user = $model['user'];
            $actor = new FeedItemActor($user['username'], 'user', $user);
            $object = new FeedItemObject('item', $model);

            $feedItem = new FeedItem($action, $actor, $object);

            $this->addObjectToFeedCollection($feedItem);
        }
    }
} 