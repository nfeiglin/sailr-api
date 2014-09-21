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
use Sailr\Api\Transformer\Transformable;
use Sailr\ApiFeed\FeedCollection;
use ReflectionClass;
use Sailr\Paginator\SailrPaginator;

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

        return $this->responder->singleObjectResponse($this->transform($model), 201);
    }

    public function showSingleModel($model) {

        $model = $this->transform($model);


       return $this->responder->singleObjectResponse($model);
    }

    public function feedResponse(FeedCollection $feedCollection, $paginator = []) {
        return
            $this->responder
                ->content($feedCollection)
                ->meta(new Collection(['pagination' => $paginator]))
                ->respond();
    }

    public function paginatedResponse(SailrPaginator $paginator, $data, $meta = []) {
        $meta = ['pagination' => $paginator->toArray()] + $meta;

        return
            $this->responder
                ->content($this->transform($data))
                ->meta(new Collection($meta))
                ->respond();
    }

    public function errorMessageResponse($message = '') {
        return $this->responder->respondWithErrorMessage($message, 400);
    }

    public function notFoundResponse($message = '') {
        return $this->responder->respondWithErrorMessage($message, 404);
    }

    public function unauthorisedResponse($message = '') {
        return $this->responder->respondWithErrorMessage($message, 403);
    }

    public function noContentSuccess(){
        return $this->responder->respond(204);
    }


    protected function transform($model) {

        if (is_iterable($model)) {

            $newCollection = new Collection();

            foreach ($model as $key => $value) {
                $newCollection[$key] = $this->transformSingleModel($value);
            }

            return $newCollection;
        }

        else {
            $model = $this->transformSingleModel($model);
        }


        return $model;
    }

    private function transformSingleModel($model) {

        if (!array_key_exists('object', $model)){
            $model['object'] = strtolower((new ReflectionClass($model))->getShortName());
        }

        if ($model instanceof Transformable) {
            $model = $model->transform();
        }

        return $model;
    }

} 