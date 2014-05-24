<?php namespace Sailr\Validator;

use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Factory;

class ValidatorServiceProvider extends ServiceProvider {

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

	}

    public function boot() {
        $this->package('sailr/validator');
        $this->app->validator->resolver(function($translator, $data, $rules, $messages)
        {
            return new SailrValidator($translator, $data, $rules, $messages);
        });
    }

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
    /*
	public function provides()
	{
		return array();
	}
    */

}
