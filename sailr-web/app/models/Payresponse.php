<?php


Namespace Feiglin;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Payresponse extends \Eloquent {
    use SoftDeletingTrait;
    protected $dates = ['created_at', 'updated_at'];
    protected $table = 'payresponse';
	protected $fillable = [];
}