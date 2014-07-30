<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Comment extends \Eloquent {
    use SoftDeletingTrait;

    public static function boot() {
        parent::boot();
        Comment::observe(new \Sailr\Observers\CommentObserver);
    }

	protected $fillable = ['user_id', 'item_id', 'comment'];
    protected $hidden = ['updated_at', 'deleted_at'];
    protected $softDelete = true;
    //protected $appends = ['user'];

    public static $rules = [
        'comment' => ['required', 'max:400'],
        'item_id' => ['required', 'exists:items,id']
    ];
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

