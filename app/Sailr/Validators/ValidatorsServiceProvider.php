<?php


namespace Sailr\Validators;

use Illuminate\Support\ServiceProvider;


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

    }

}