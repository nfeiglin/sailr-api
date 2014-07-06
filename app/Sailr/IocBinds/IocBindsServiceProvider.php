<?php


namespace Sailr\IocBinds;

use Illuminate\Support\ServiceProvider;
use Sailr\Emporium\Merchant\Merchant;
use Sailr\Entity\RedirectEntity;
use Sailr\Validators\PurchaseValidator;

class IocBindsServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['PurchaseValidator'] = $this->app->share(function($app) {
            return new PurchaseValidator;
        });


        $this->app->bindShared('Sailr\Validators\PurchaseValidator', function($app) {
            return $this->app->make('PurchaseValidator');
        });


        $this->app['merchant'] = $this->app->share(function($app) {
           return new Merchant($this->app->make('PurchaseValidator'), new RedirectEntity);
        });



        $this->app->bindShared('Sailr\Emporium\Merchant\Merchant', function($app) {
            return $this->app->make('merchant');
        });

       /* $this->app->bind('\BuyController', function($app) {
           return new \BuyController($this->app->make('Sailr\Validators\PurchaseValidator'), $this->app->make('merchant'));
        });
       */
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['PurchaseValidator', 'merchant'];
    }

}