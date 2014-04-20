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


Route::get('/', function () {
    return View::make('hello');
});


Route::controller('password', 'RemindersController');
Route::resource('buy', 'BuyController', ['only' => ['create', 'store', 'show']]);

Route::post('login', 'SessionController@store');
Route::get('logout', 'SessionController@destroy');

Route::group(array('prefix' => 'api', 'before' => 'json_auth'), function () {

    Route::resource('session', 'SessionController', array('only' => array('store', 'destroy')));
    Route::resource('users/profile/image', 'ProfileImageController', ['only' => ['store', 'destroy']]);
    Route::resource('users', 'UsersController');


    Route::get('users/self/feed', 'UsersController@self_feed');

    Route::resource('comments', 'CommentsController', ['only' => ['store','show', 'destroy']]);
    Route::post('login', 'SessionController@store');
    Route::get('logout', 'SessionController@destroy');


    Route::match(['GET', 'POST'], 'relationship/show', 'RelationshipsController@show');
    Route::match(['GET', 'POST'], 'relationship/store', 'RelationshipsController@store');
    Route::match(['GET', 'POST'], 'relationship/destroy', 'RelationshipsController@destroy');


    Route::resource('items', 'ItemsController');
    Route::get('items/{id}/comments', 'CommentsController@item_comments');
    Route::get('users/{id}/items', 'UsersController@items');




});
