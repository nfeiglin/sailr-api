<?php

namespace Sailr\Observers;

use Notification;


class CommentObserver extends BaseObserver
{

    public function creating($commentModel)
    {
        $loggedInUserId = \Auth::user()->id;
        $product = $commentModel->item();
        $seller = $product->user();
        //$commenter = $commentModel->user();

        //Only notify the seller that a new comment has been made on their product if they didn't post it themselves
        if ($commentModel->user_id != $seller->id) {

            //Notify the seller
            Notification::create([
                'short_text' => 'New comment on ' . $product->title,
                'type' => 'comment.create',
                'user_id' => $seller->id,
                'data' => ['comment' => $commentModel, 'item' => $product],
            ]);
            //Email the seller telling them someone has commented
        }

        //Now notifiy anyone that was tagged in the comment, except if they tagged themself
        $tagger = \Sailr\Tags\SailrTagger::make();
        $taggedUsers = $tagger->getTaggedUsers($commentModel->comment);

        foreach ($taggedUsers as $taggedUser) {

            //Ignore notifiying the commenter if they tagged themself

            if ($taggedUser->id == $loggedInUserId) {
                continue;
            }

            //Notify them
            Notification::create([
                'short_text' => 'You were tagged in a comment on ' . $product->title,
                'type' => 'comment.create',
                'user_id' => $taggedUser->id,
                'data' => ['comment' => $commentModel, 'item' => $product],
            ]);

            //Now email them...


        }
    }
}