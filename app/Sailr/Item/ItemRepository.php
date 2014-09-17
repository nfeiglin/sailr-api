<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 17/09/2014
 * Time: 9:56 PM
 */

namespace Sailr\Item;


use Laracasts\Commander\Events\DispatchableTrait;
use Sailr\Repository\BaseRepository;
use Laracasts\Commander\Events\EventGenerator;
use Sailr\Item\Events\ItemWasAdded;
use Item;

class ItemRepository extends BaseRepository {
    use DispatchableTrait;

    public function __construct(Item $model) {
        $this->model = $model;
    }
    public function post($title, $currency, $price, $user_id) {

        $this->model->title = $title;
        $this->model->currency = $currency;
        $this->model->price = $price;
        $this->model->user_id = $user_id;
        $this->model->initial_units = 1;

        $this->model->save();

        $this->raise(new ItemWasAdded($this->model));

        return $this->model;
    }
} 