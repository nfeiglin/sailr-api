<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;
use Sailr\Observers\CommentObserver;

class Comment extends \Eloquent {
    use SoftDeletingTrait;

    public static function boot() {
        parent::boot();
        Comment::observe(new CommentObserver);
    }

	protected $fillable = ['user_id', 'item_id', 'comment'];
    protected $hidden = ['updated_at', 'deleted_at'];
    protected $softDelete = true;
    //protected $appends = ['user'];

    public function user() {
        return $this->belongsTo('User');
    }

    public function item() {
        return $this->belongsTo('Item');
    }


    public function getUserAttribute() {
        return User::find($this->user_id)->toArray();
    }


}

