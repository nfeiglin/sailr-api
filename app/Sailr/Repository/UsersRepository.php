<?php


namespace Sailr\Repository;


use Illuminate\Hashing\HasherInterface;
use User;

class UsersRepository extends BaseRepository {

    /**
     * @var HasherInterface
     */
    protected $hasher;

    /**
     * @param \User $model
     * @param HasherInterface $hasher
     */
    public function __construct(User $model, HasherInterface $hasher) {
        $this->model = $model;
        $this->hasher = $hasher;
    }

    /**
     * @param $input
     * @return User the newly created User model
     */
    public function create($input) {
        $this->model->name = $input['name'];
        $this->model->username = $input['username'];
        $this->model->email = $input['email'];
        $this->model->bio = $input['bio'];
        $this->model->password = $this->hasher->make($input['password']);

        $this->model->save();

        return $this->model;
    }

}