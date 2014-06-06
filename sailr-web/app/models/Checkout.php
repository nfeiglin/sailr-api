<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Checkout extends \Eloquent {
    use SoftDeletingTrait;

	protected $fillable = [];
}