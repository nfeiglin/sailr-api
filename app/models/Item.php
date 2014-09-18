<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;
use Sailr\Api\Transformer\Transformable;

class Item extends Eloquent implements Transformable
{
    use SoftDeletingTrait;

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

    public function collections() {
        return $this->belongsToMany('Collection')->withTimestamps();
    }

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

    public function transform() {
        $transformer = new \Sailr\Item\ItemTransformer($this);
        return $transformer->transform();
    }
}
