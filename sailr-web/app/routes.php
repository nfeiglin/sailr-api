<?php
use Intervention\Image\Facades;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/


View::composer('*', function($view) {
	if(!array_key_exists('hasNavbar', $view->getData())) {
		$view->with('hasNavbar', 1);
	}
});

Route::get('/', function () {
    return View::make('index');
});

Route::get('test', function() {
   return View::make('test')->with('title', 'Test title')->with('hasNavbar', 1);
});


Route::controller('password', 'RemindersController');
Route::resource('buy', 'BuyController', ['only' => ['create', 'store', 'show']]);

Route::resource('user', 'UsersController', ['only' => ['create', 'store', 'show']]);

Route::resource('session', 'SessionController', ['only' => ['create', 'store']]);
Route::get('login', 'SessionController@create');
Route::get('logout', 'SessionController@destroy');


Route::get('/', 'UsersController@self_feed', ['before' => ['auth']]);

