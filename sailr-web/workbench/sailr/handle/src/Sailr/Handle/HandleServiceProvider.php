<?php namespace Sailr\Handle;

use Illuminate\Support\ServiceProvider;

class HandleServiceProvider extends ServiceProvider {

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

		$this->app->events->subscribe(new EventHandler($this->app));
        /*

        $this->app->events->subscribe = $this->app->share(function() {
           return new EventHandler;
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
		return array();
	}

}