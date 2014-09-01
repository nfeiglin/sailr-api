<?php


namespace Sailr\Repository;


class UsersRepository extends BaseRepository {

    public function __construct(\User $model) {
        $this->model = $model;
    }
}