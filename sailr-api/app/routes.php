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
/*
Event::listen('illuminate.query', function ($query, $params, $time, $conn) {
    print_r(array($query, $params, $time, $conn));
    print('End of Queary <br>');
});
*/

Route::get('/', function () {
    return View::make('hello');
});


Route::get('login', function () {
    return "login.create";
});

Route::controller('password', 'RemindersController');

Route::post('login', 'AuthController@store');
Route::get('logout', 'AuthController@destroy');

Route::group(array('prefix' => 'api', 'before' => 'json_auth'), function () {
    // Route::resource('auth', 'AuthController', array('only' => array('store', 'destroy')));

    Route::post('login', 'AuthController@store');
    Route::get('logout', 'AuthController@destroy');


    Route::post('relationship/show', 'RelationshipsController@show');
    Route::post('relationship/store', 'RelationshipsController@store');
    Route::post('relationship/destroy', 'RelationshipsController@destroy');


    Route::resource('items', 'ItemsController');
    Route::delete('item/{id}', 'ItemsController@destroy');

    Route::resource('user', 'UsersController');
    Route::get('user/{id}', 'UsersController@show');

    Route::get('{username}/{item_id}', 'ItemsController@show');
    Route::get('user/self/feed', 'UsersController@self_feed');
    Route::post('user/profile/image', 'UsersController@set_profile_image');


});
