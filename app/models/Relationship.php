<?php


class Relationship extends \Eloquent
{

    protected $fillable = [];
    protected $dates = ['created_at', 'updated_at'];
    public function user()
    {
        return $this->belongsTo('User');
    }
}