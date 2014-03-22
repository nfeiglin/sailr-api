<?php


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

Event::listen('illuminate.query', function ($query, $params, $time, $conn) {
    print_r(array($query, $params, $time, $conn));
    print('End of Queary <br>');
});

Route::get('/', function () {
    return View::make('hello');
});

Route::group(array('prefix' => 'api'), function() {
    Route::resource('items', 'ItemsController');
    Route::resource('user', 'UsersController');
    Route::get('user/self/feed', 'UsersController@self_feed');
});
