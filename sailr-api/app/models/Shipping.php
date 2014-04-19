<?php

class Shipping extends \Eloquent {
    protected $table = 'shippings';
	protected $fillable = ['item_id', 'type', 'price'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected $softDelete = true;

    public function item()
    {
        return $this->belongsTo('Item');
    }

    public static $shippingTypes = [
          'Domestic' => ['domestic_shipping_price', 'domestic_shipping_desc'],
          'International' => ['international_shipping_price', 'international_shipping_desc']
        ];
}