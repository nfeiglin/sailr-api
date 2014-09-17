<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 17/09/2014
 * Time: 11:06 PM
 */

namespace Sailr\Item;


use Illuminate\Support\Collection;
use Sailr\Api\Transformer\Transformer;

class ItemTransformer {

    public function __construct($model) {
        return $this->transform($model);
    }

    public function transform(Collection $model) {
        //Leaving this here for future implementation
        return $model;
    }
} 