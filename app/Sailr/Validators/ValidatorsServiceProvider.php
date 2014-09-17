<?php


namespace Sailr\Validators;

use Illuminate\Support\ServiceProvider;
use Sailr\Validators\Exceptions\ValidatorException;
use Sailr\Api\Responses\ApiResponse;
use Sailr\Api\Support\ErrorCollection;
class ValidatorsServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Sailr\Validators\PurchaseValidator', function() {
            return new PurchaseValidator;
        });

        $this->app->error(function(ValidatorException $validatorException){
            $errors = $validatorException->getValidator()->errors();
            //Now, lets tell the user

            $errorCollection = new ErrorCollection($errors->first(), $errors->toArray());
           return ApiResponse::make()->validationErrorResponse($errorCollection);
        });

    }

}