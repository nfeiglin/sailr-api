<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 31/08/2014
 * Time: 3:17 AM
 */

namespace Sailr\Repository;
use Laracasts\Commander\Events\EventGenerator;
class BaseRepository {
    use EventGenerator;

    /**
     * Class BaseRepository
     * @package Sailr\Repository
     */

    /**
     * @var \Model
     */
    protected $model;

    public function __construct(\Model $model) {
        $this->model = $model;
    }

    public function setQueryBuilderInstance($queryBuilder) {
        $this->model = $queryBuilder;
    }

    /**
     * Make a new instance of the entity to query on
     *
     * @param array $with
     */
    public function make(array $with = [])
    {
        return $this->model->with($with);
    }

    /**
     * Find an entity by id
     *
     * @param int $id
     * @return Illuminate\Database\Eloquent\Model
     */
    public function getById($id)
    {
        return $this->model->find($id);
    }

    /**
     * Find a single entity by key value
     *
     * @param string $key
     * @param string $value
     * @param array $fields
     * @param array $with
     */
    public function getFirstBy($key, $value, array $fields = ['*'], array $with = array())
    {
        return $this->make($with)->where($key, '=', $value)->first($fields);
    }

    /**
     * Find a single entity by key value or throw an error if it doesn't exist
     *
     * @param string $key
     * @param string $value
     * @param array $fields
     * @param array $with
     */
    public function getFirstOrFailBy($key, $value, array $fields = ['*'], array $with = array())
    {
        return $this->make($with)->where($key, '=', $value)->firstOrFail();
    }

    /**
     * Find many entities by key value
     *
     * @param string $key
     * @param string $value
     * @param array $with
     */
    public function getManyBy($key, $value, array $with = array())
    {
        return $this->make($with)->where($key, '=', $value)->get();
    }

    /**
     * Return all results that have a required relationship
     *
     * @param string $relation
     */
    public function has($relation, array $with = array())
    {
        $entity = $this->make($with);

        return $entity->has($relation)->get();
    }

    /**
     * Get Results by Page
     *
     * @param int $page
     * @param int $limit
     * @param array $with
     * @return \StdClass Object with $items and $totalItems for pagination
     */
    public function getByPage($page = 1, $limit = 10, $with = array())
    {
        //Based off http://culttt.com/2014/03/17/eloquent-tricks-better-repositories/

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->make($with);

        $model = $query->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        $result->totalItems = $this->model->count();
        $result->items = $model->all();

        return $result;
    }

} 