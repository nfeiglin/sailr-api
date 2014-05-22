<?php namespace Sailr\Holystone;

use Illuminate\Support\ServiceProvider;

class HolystoneServiceProvider extends ServiceProvider {

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('sailr/holystone', 'holystone');
    }

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
		$this->app['holystone'] = $this->app->share(function($app) {
            return new Holystone();
        });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['holystone'];
	}

}
