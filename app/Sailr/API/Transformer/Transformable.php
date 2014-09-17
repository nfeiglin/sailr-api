<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 17/09/2014
 * Time: 11:04 PM
 */

namespace Sailr\Api\Transformer;

use Illuminate\Database\Eloquent\Model;

interface Transformable {

    /**
     * @return Model
     */
    public function transform();
}