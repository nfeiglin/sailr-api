<?php

class Comment extends \Eloquent {
	protected $fillable = ['user_id', 'item_id', 'comment'];
    protected $hidden = ['id', 'item_id'];
    protected $softDelete = true;

    public static $rules = [
        'comment' => ['required', 'max:400']
    ];
    public function user() {
        return $this->belongsTo('User');
    }

    public function item() {
        return $this->belongsTo('Item');
    }
}