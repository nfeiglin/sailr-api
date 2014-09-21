<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 31/08/2014
 * Time: 3:17 AM
 */

namespace Sailr\Repository;
use Laracasts\Commander\Events\EventGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination;

class BaseRepository {
    use EventGenerator;

    /**
     * Class BaseRepository
     * @package Sailr\Repository
     */

    /**
     * @var Eloquent
     */
    protected $model;

    protected $primaryKeyField = 'id';

    public function __construct(\Eloquent $model) {
        $this->model = $model;
    }

    public function setQueryBuilderInstance($queryBuilder) {
        $this->model = $queryBuilder;
    }

    /**
     * Make a new instance of the entity to query on
     *
     * @param array $with
     * @return Eloquent
     */
    public function make(array $with = [])
    {
        return $this->model->with($with);
    }

    /**
     * Find an entity by id
     *
     * @param int $id
     * @return Model
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
     * @return Model
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
     * @return Model
     */
    public function getFirstOrFailBy($key, $value, array $fields = ['*'], array $with = array())
    {
        return $this->make($with)->where($key, '=', $value)->firstOrFail($fields);
    }


    /**
     * Find many entities by key value
     *
     * @param string $key
     * @param string $value
     * @param array $with
     * @return Model
     */
    public function getManyBy($key, $value, array $with = array())
    {
        return $this->make($with)->where($key, '=', $value)->get();
    }

    /**
     * Return all results that have a required relationship
     *
     * @param string $relation
     * @param array $with
     * @return Model
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
    public function getByPage($page = 1, $limit = 25, $with = array())
    {

        //Based off http://culttt.com/2014/03/17/eloquent-tricks-better-repositories/

          $results = new \StdClass;

          $results->page = $page;
          $results->limit = $limit;
          $results->totalItems = 0;
          $results->items = array();

          $users = $this->model->skip($limit * ($page - 1))->take($limit)->get();

           //PHP array count -- not querying the DB for this
          $results->totalItems = $this->model->count();
          $results->items = $users->all();

          return $results;


    /**
     * @param array $columns
     * @return Eloquent\Collection|static[]
     */
    public function getResults($columns = []) {
        return $this->model->get($columns);
    }

    /**
     * @param array $columns
     * @return Eloquent\Collection|static[]
     * @see getResults
     */
    public function get($columns = []) {
        return $this->getResults($columns);
    }

    public function getFirst($columns = []) {
        return $this->model->first($columns);
    }

    public function getFirstOrFail($columns = []) {
        return $this->model->firstOrFail($columns);
    }


    /**
     * @param int $perPage
     * @param array $columns
     * @return Paginator
     */
    public function getSimplePaginated($perPage = 25, $columns = []) {
        return $this->model->simplePaginate($perPage, $columns);
    }

    /**
     * @param int $perPage
     * @param array $columns
     * @return Paginator
     */
    public function getAdvancedPaginated($perPage = 25, $columns = []) {
        return $this->model->paginate($perPage, $columns);
    }



} 