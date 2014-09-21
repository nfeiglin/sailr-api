<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 17/09/2014
 * Time: 9:56 PM
 */

namespace Sailr\Item;


use Laracasts\Commander\Events\DispatchableTrait;
use Sailr\Item\Events\ItemWasViewed;
use Sailr\Repository\BaseRepository;
use Laracasts\Commander\Events\EventGenerator;
use Sailr\Paginator\SailrPaginator;
use Sailr\Item\Events\ItemWasAdded;
use Item;
use User;
use App;

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


    public function findOneWithPhotosAndUser($id) {
       $item = $this->model->with(array(
            'Photos' => function ($y) {
                $y->where('type', '=', 'full_res');
                $y->select(['item_id', 'set_id', 'type', 'url']);
            },
            'User' => function ($x) {
                $x->with(['ProfileImg' => function($p){
                    $p->where('type', 'medium');
                }]);
                $x->select(['id', 'name', 'username']);
            },
        ))->where('id', '=', $id)->firstOrFail();

        $this->raise(new ItemWasViewed($item));

        return $item;
    }

    /**
     * @param User | int $user_id
     * @return SailrPaginator
     */
    public function getAllItemsForUserPaginated($user_id) {
        if ($user_id instanceof User) {
            $user_id = $user_id->getAuthIdentifier();
        }

        $paginatorWithResults = $this->make(
            [
                'User' => function($u) {
                  $u->select(['id', 'name', 'username']);
                },
                'Photos' => function($y) {
                    $y->where('type', '=', 'full_res');
                    $y->select(['url', 'set_id', 'item_id']);
                 }

            ])->where('public', '=', 1)->where('user_id', '=', $user_id)->orderBy('created_at', 'DESC')->simplePaginate(25);

        return (new SailrPaginator($paginatorWithResults->getCollection(), $paginatorWithResults, App::make('Illuminate\Routing\UrlGenerator')));
    }
} 