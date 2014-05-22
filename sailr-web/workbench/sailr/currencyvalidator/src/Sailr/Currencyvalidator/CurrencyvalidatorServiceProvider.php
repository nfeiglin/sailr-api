<?php namespace Sailr\Currencyvalidator;

use Illuminate\Support\ServiceProvider;

class CurrencyvalidatorServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->app->validator->resolver(function($translator, $data, $rules, $messages)
        {
            return new CurrencyValidator($translator, $data, $rules, $messages);
        });
	}

    public function boot()
    {

    }

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
