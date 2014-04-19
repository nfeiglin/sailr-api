<?php

class Shipping extends \Eloquent {
	protected $fillable = ['item_id', 'type', 'price'];
    protected $softDelete = true;

    public function item()
    {
        return $this->belongsTo('Item');
    }
}