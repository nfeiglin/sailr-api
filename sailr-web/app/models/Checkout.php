<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Checkout extends \Eloquent {
    use SoftDeletingTrait;
    protected $dates = ['created_at', 'updated_at'];
    protected $hidden = [];
	protected $fillable = [];

    public function user()
    {
        return $this->belongsTo('User');
    }

    public function item()
    {
        return $this->belongsTo('Item');
    }
}