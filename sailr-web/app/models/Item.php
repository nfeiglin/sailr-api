<?php

class Item extends Eloquent
{
    protected $hidden = array('updated_at', 'deleted_at', 'user_id');
    protected $fillable = ['user_id', 'price', 'currency', 'initial_units', 'description', 'title', 'ships_to', 'public', 'ship_price'];
    protected $softDelete = true;
    //protected $appends = ['comments'];

    public function user()
    {
        return $this->belongsTo('User');
    }

    public function shipping() {
        return $this->hasMany('Shipping');
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
        'currency' => 'required|alpha|min:3|max:3|in:AUD,CAD,EUR,GBP,JPY,USD,NZD,CHF,HKD,SGD,SEK,DKK,PLN,NOK,HUF,CZK,ILS,MXN,PHP,TWD,THB,RUB',
        'initial_units' => 'required|min:1|max:9999999',
        'country' => 'required|country',
        'domestic_shipping_price' => 'required|numeric|min:0|max:9999',
        'domestic_shipping_desc' => 'required|max:400',
        'international_shipping_price' => 'required|numeric|min:0|max:9990',
        'international_shipping_desc' => 'required|max:400'
    );

    public function getCommentsAttribute() {
        $comments = Comment::where('item_id', '=', $this->id);

        return $this->attributes['comments'] = $comments;
    }

    public function scopeWhereUser($query, User $user) {
        return $query->where('user_id', '=', $user->id);
    }
}
