<?php


namespace Sailr\Observers;


interface ObservationInterface {

    public function saving($model);
    public function creating($model);
    public function updating($model);
} 