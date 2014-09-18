<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 11/09/2014
 * Time: 7:34 PM
 */

namespace Sailr\Api\Responses;

use Illuminate\Support\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Contracts\ArrayableInterface;
use ReflectionClass;
use Sailr\Api\Support\ErrorCollection;
use Sailr\Api\Transformer\Transformable;

/**
 * Class Response
 * @package Sailr\Api\Responses
 */

class ApiResponse {

    /**
    * @var Collection A collection of the content that should be in the 'meta' key of the response
    */
    protected $metaContent;

    /**
     * @var Collection A collection of the content that should be in the 'data' key of the response
     */
    protected $dataContent;

    /**
     * @var array The headers to be sent with the response
     */
    protected $headers;


    /**
     * @var array An associative array of the entire response body to be sent
     */
    protected $responseBody;

    /**
     * @var JsonResponse A JsonResponse instance
     */
    protected $JsonResponse;

    public function __construct(JsonResponse $JsonResponse, Collection $dataContent, Collection $metaContent, array $responseBody = [], array $headers = [])
    {
        $this->JsonResponse = $JsonResponse;
        $this->dataContent = $dataContent;
        $this->metaContent = $metaContent;
        $this->responseBody = $responseBody;
        $this->headers = $headers;
    }

    public static function make() {
        return new static(new JsonResponse(), new Collection(), new Collection());
    }



    public function respond($statusCode = 200) {

        if ($statusCode === 204) {
            return $this->JsonResponse->create(null, 204);
        }
        ///dd($this->getMetaContent()->toArray());

        $this->responseBody['meta'] = $this->getMetaContent()->toArray();
        $this->responseBody['data'] = $this->getDataContent()->toArray();


        return $this->JsonResponse->create($this->responseBody, $statusCode, $this->headers);
    }

    public function validationErrorResponse(ErrorCollection $errorCollection, $statusCode = 400) {


        $meta = $this->getMetaContent();

        $meta['error'] = new Collection([
            'message' => $errorCollection->getMessage(),
            'errors' => $errorCollection->getErrors()

        ]);

        $this->setMetaContent($meta);

        return $this->respond($statusCode);
    }

    public function respondWithErrorMessage($message, $statusCode = 400) {

        $errorCollection = new ErrorCollection($message, []);

        $meta = $this->getMetaContent();

        $meta['error'] = new Collection([
            'message' => $errorCollection->getMessage(),
            'errors' => $errorCollection->getErrors()

        ]);

        $this->setMetaContent($meta);

        return $this->respond($statusCode);
    }

    public function singleObjectResponse($model, $statusCode = 200) {
        if (!$model instanceof Collection && $model instanceof ArrayableInterface) {
            $model = new Collection($model->toArray());
        }
        return $this->content($model)->respond($statusCode);
    }

    public function content($content = null) {
        if (is_null($content)) {
            return $this->getDataContent();
        }

        $this->setDataContent($content);
        return $this;
    }

    public function meta($meta = null) {
        if (is_null($meta)) {
            return $this->getMetaContent();
        }
        $this->setMetaContent($meta);
        return $this;
    }


    /**
     * @return Collection
     */
    public function getMetaContent()
    {
        return $this->metaContent;
    }

    /**
     * @param Collection $metaContent
     */
    public function setMetaContent($metaContent)
    {
        $this->metaContent = $metaContent;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    /**
     * @return Collection
     */
    public function getDataContent()
    {
        return $this->dataContent;
    }

    /**
     * @param Collection $dataContent
     */
    public function setDataContent($dataContent)
    {
        $this->dataContent = $dataContent;
    }


} 