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
        $events->listen('notification.index', 'Sailr\Handle\EventHandler@onNotificationIndexView');
        $events->listen('illuminate.log', 'Sailr\Handle\EventHandler@onLoggingEvent');

    }

   public function onUserCreate(\User $user) {
        \Mail::queue('emails.user.welcome', $user->toArray(), function($message) use ($user) {
            $message->to($user->email, $user->name)->subject('Welcome to Sailr');
        });
    }

    public function onRelationshipCreate(\Relationship $relationship) {
        $follower = \User::where('id', '=', $relationship->user_id)->firstOrFail(['id', 'username', 'name','bio']);
        $following = \User::where('id', '=', $relationship->follows_user_id)->firstOrFail(['id', 'username', 'name', 'email']);

        $data = ['follower' => $follower->toArray(), 'following' => $following->toArray()];

        $relationshipID = $relationship->id;

        \Queue::push(function($job) use ($relationshipID) {

            $relationship = \Relationship::findOrFail($relationshipID);
            $follower = \User::where('id', '=', $relationship->user_id)->firstOrFail(['username']);
            $relationship->setHidden(array_merge($relationship->getHidden(), ['updated_at', 'created_at']));

            \Notification::create([
                'short_text' => 'Followed by ' . $follower->username,
                'type' => 'user.follow',
                'user_id' => $relationship->follows_user_id,
                'data' => ['relationship' => $relationship->toArray()],
            ]);

            $job->delete();
        });

        \Mail::queue('emails.relationship.newFollower', $data, function($message) use ($follower, $following) {
            $message->to($following->email)->subject($follower->name . ' (' . $follower->username . ') ' . 'is now following you on Sailr');
        });
    }

    public function onNotificationIndexView($userID) {

        /* Set all the users notifications to viewed */
        $wheres = ['user_id' => ['$in' => [$userID]], 'viewed' => ['$exists' => false]];
        $changeTo = ['$set' => ['viewed' => true]];
        $options = ['multi' => true];
        $sailrDB = \DB::connection('mongodb');
        $notificationsCollection = $sailrDB->selectCollection('notifications');
        $notificationsCollection->update($wheres, $changeTo, $options);
    }

    public function onLoggingEvent($level, $message, $context) {

        \Queue::push(function($job) use ($level, $message, $context){

            \Config::set('mail.driver', 'mailgun'); //Let's send these with Mailgun rathern than Mandrill to lessen the load


            $data = ['level' => $level, 'logMessage' => $message, 'context' => $context];

            \Mail::send('emails.admin.log', $data, function($message) use ($level) {
                $message->from(['tech@sailr.co' => 'Sailr Log-bot']);
                $message->to(\Config::get('admin.email'));
                $message->subject('A log from Sailr has been recorded. Level: ' . $level);
            });


            \Config::set('mail.driver', 'mandrill');

            $job->delete();
        });


    }
} 