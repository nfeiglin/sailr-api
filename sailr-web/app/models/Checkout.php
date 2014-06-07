<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Checkout extends \Eloquent {
    use SoftDeletingTrait;
    protected $dates = ['created_at', 'updated_at'];

	protected $fillable = [];
}