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
    return View::make('index');
});

Route::get('test', function() {
   return View::make('test')->with('title', 'Test title');
});


Route::controller('password', 'RemindersController');
Route::resource('buy', 'BuyController', ['only' => ['create', 'store', 'show']]);

Route::resource('session', 'SessionController', ['only' => ['create', 'store']]);
Route::get('login', 'SessionController@create');
Route::get('logout', 'SessionController@destroy');

