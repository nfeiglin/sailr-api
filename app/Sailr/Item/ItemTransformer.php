<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 17/09/2014
 * Time: 11:06 PM
 */

namespace Sailr\Item;


use Illuminate\Support\Collection;
use Carbon\Carbon;
use DateTime;

class ItemTransformer {

    /**
     * @var Collection
     */
    public $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function transform() {
        return $this->model;
    }
} 