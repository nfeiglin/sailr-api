<?php

namespace Sailr\Observers;
use Sailr\Holystone\Facades\Holystone;


class CollectionObserver extends BaseObserver
{

    protected function purifiyModelText($model) {
        if(isset($model->title)) {
            $model->title = Holystone::sanitize($model->title);
        }

        return $model;
    }
    public function saving($collectionModel)
    {
        $collectionModel = $this->purifiyModelText($collectionModel);
        return $collectionModel;
    }
}