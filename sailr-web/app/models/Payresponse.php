<?php


Namespace Feiglin;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Payresponse extends \Eloquent {
    use SoftDeletingTrait;

    protected $table = 'payresponse';
	protected $fillable = [];
}