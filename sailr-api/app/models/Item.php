<?php

class Item extends Eloquent {
	protected $guarded = array();
    protected $fillable = array('description');
	protected $softDeletes = true;
	public static $rules = array();

    public function user(){
        return $this->belongsTo('User');
    }
}
