<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 17/09/2014
 * Time: 11:31 PM
 */

namespace Sailr\Api\Responses;

use Illuminate\Support\Collection;
use Sailr\Api\Responses\ApiResponse;
use Sailr\ApiFeed\FeedCollection;
use ReflectionClass;

class Responder {

    /**
     * @var ApiResponse $responder
     */
    protected $responder;

    public function __construct(ApiResponse $responder)
    {
        $this->responder = $responder;
    }

    public function createdModelResponse($model) {

        if (is_null($model['object'])){
            $model['object'] = strtolower((new ReflectionClass($model))->getShortName());
        }

        if ($model instanceof Transformable) {
            $model = $model->transform();
        }

        return $this->responder->singleObjectResponse($model, 201);
    }

    public function feedResponse(FeedCollection $feedCollection, $paginator = []) {
        $response = $this->responder->content($feedCollection);
        $response->meta(new Collection(['pagination' => $paginator]));
        return $response->respond();
    }

} 