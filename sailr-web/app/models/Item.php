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


    public function photos()
    {
        return $this->hasMany('Photo');
    }

    public function comment() {
        return $this->hasMany('Comment');
    }

    public static $updateRules = [
        'title' => ['sometimes', 'max:255'],
        'price' => ['sometimes', 'min:0', 'max:999999', 'numeric'],
        'ship_price' => ['sometimes', 'min:0', 'max:999999', 'numeric'],
        'currency' => ['sometimes', 'currency'],
        'ships_to' => ['sometimes', 'countryCode'],

    ];

    public static $publishRules = [
        'title' => ['required', 'max:255'],
        'price' => ['required', 'min:0', 'max:999999', 'numeric'],
        'ship_price' => ['required', 'min:0', 'max:999999', 'numeric'],
        'currency' => ['required', 'currency'],
        'ships_to' => ['required', 'countryCode'],

    ];

    public function getCommentsAttribute() {
        $comments = Comment::where('item_id', '=', $this->id);

        return $this->attributes['comments'] = $comments;
    }

    public function scopeWhereUser($query, User $user) {
        return $query->where('user_id', '=', $user->id);
    }

    public function scopeWhereLike($query, $column, $value) {
        return $query->where($column, 'LIKE', '%' . $value . '%');
    }

    public function scopeOrWhereLike($query, $column, $value) {
        return $query->orWhere($column, 'LIKE', '%' . $value . '%');
    }
}
