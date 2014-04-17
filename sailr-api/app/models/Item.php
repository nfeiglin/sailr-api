<?php

class Item extends Eloquent
{
    protected $hidden = array('updated_at', 'deleted_at', 'user_id');
    protected $fillable = ['user_id', 'price', 'currency', 'initial_units', 'description', 'title'];
    protected $softDelete = true;

    public function user()
    {
        return $this->belongsTo('User');
    }

    public function photos()
    {
        return $this->hasMany('Photo');
    }

    public function comment() {
        return $this->hasMany('Comment');
    }

    public static $rules = array(
        'title' => 'required|max:40',
        'description' => 'required|max:240',
        'price' => 'required|numeric|min:0.00|max:999999999',
        'currency' => 'required|alpha|min:3|max:3',
        'initial_units' => 'required|min:1|max:9999999',
    );
}
