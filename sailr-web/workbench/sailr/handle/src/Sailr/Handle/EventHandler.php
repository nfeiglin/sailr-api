<?php

namespace Sailr\Handle;

class EventHandler {
    protected $app;

    public function ___construct($app = null) {
        $this->app = $app;
    }

    public function subscribe($events) {
        $events->listen('user.create', 'Sailr\Handle\EventHandler@onUserCreate');
        $events->listen('relationship.create', 'Sailr\Handle\EventHandler@onRelationshipCreate');

    }

   public function onUserCreate(\User $user) {
        \Mail::queue('emails.user.welcome', $user->toArray(), function($message) use ($user) {
            $message->to($user->email, $user->name)->subject('Welcome to Sailr');
        });
    }

    public function onRelationshipCreate(\Relationship $relationship) {
        $follower = \User::where('id', '=', $relationship->user_id)->firstOrFail(['id', 'username', 'name']);
        $following = \User::where('id', '=', $relationship->follows_user_id)->firstOrFail(['id', 'username', 'name', 'email']);

        $data = ['follower' => $follower->toArray(), 'following' => $following->toArray()];

        \Queue::push(function($job) use ($relationship, $follower, $following) {

            $relationship->setHidden(array_merge($relationship->getHidden(), ['updated_at', 'created_at']));

            \Notification::create([
                'short_text' => 'Followed by ' . $follower->username,
                'data' => ['relationship' => $relationship->toArray()],
                'type' => 'user.follow',
                'user_id' => $relationship->user_id
            ]);

            $job->delete();
        });

        \Mail::queue('emails.relationship.newFollower', $data, function($message) use ($follower, $following) {
            $message->to($following->email)->subject($follower->name . ' (' . $follower->username . ') ' . 'is now following you on Sailr');
        });
    }
} 