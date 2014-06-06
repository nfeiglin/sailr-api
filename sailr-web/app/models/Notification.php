<?php

use Jenssegers\Mongodb\Eloquent\SoftDeletingTrait;

class Notification extends Jenssegers\Mongodb\Model {
    use SoftDeletingTrait;

    protected $connection = 'mongodb';
    protected $timestamps = true;
	protected $fillable = [];

    public static function boot() {
        parent::boot();

        Notification::creating(function(Notification $notification) {
            if(Auth::check()) {
                $notification->user_id = Auth::user()->id;
            }

            return true;
        });
    }
}