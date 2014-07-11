<?php
namespace Sailr\TestPipe;

use \Illuminate\Support\ServiceProvider;

class TestPipeServiceProvider extends ServiceProvider {

    public function boot() {
        $this->package('sailr/testpipe');
    }

    public function register() {
        $this->app['TestPipe'] = $this->app->share(function($app) {
            return new TestPipe($this->app['files'], $this->app['config']->get('sailr/testpipe::paths'));
        });

        $this->app->bindShared('Sailr\TestPipe\TestPipeController', function() {
            return new TestPipeController($this->app['TestPipe']);
        });

        $this->app->bind('Sailr\TestPipe\TestPipe', function($app) {
            return new TestPipe($this->app['files'], $this->app['config']->get('sailr/testpipe::paths'));
        });
    }

    public function provides() {
        return ['testpipe'];
    }
} 