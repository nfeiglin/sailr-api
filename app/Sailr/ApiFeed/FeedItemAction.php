<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 1/09/2014
 * Time: 10:22 PM
 */

namespace Sailr\ApiFeed;
use Carbon\Carbon;
use Illuminate\Support\Contracts\ArrayableInterface;


class FeedItemAction implements FeedItemActionInterface, ArrayableInterface {

    /**
     * @var $code string The code of the action (i.e item.create)
     * @var $title string The title used to describe the action (i.e Added new item)
     * @var $time Carbon A carbon object representing the time the action was taken
     */

    public $code;
    public $title;
    public $time;

    public function __construct($code, $title, Carbon $time) {
        $this->code = $code;
        $this->title = $title;
        $this->time = $time;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return Carbon
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }



    public function toArray() {
        return [
            'code' => $this->getCode(),
            'title' => $this->getTitle(),
            'time' => $this->getTime()->toDateTimeString()
        ];
    }
} 