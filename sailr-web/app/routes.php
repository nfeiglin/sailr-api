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
Event::listen('illuminate.query', function($sql, $bindings, $time){
    //echo $sql;          // select * from my_table where id=? 
    //print_r($bindings); // Array ( [0] => 4 )
    echo $time;         // 0.58 

    // To get the full sql query with bindings inserted
    $sql = str_replace(array('%', '?'), array('%%', '%s'), $sql);
    $full_sql = vsprintf($sql, $bindings);
    echo '<pre>' . $full_sql . '</pre>';
});
*/

View::composer('*', function($view) {
	if(!array_key_exists('hasNavbar', $view->getData())) {
		$view->with('hasNavbar', 1);
	}
});

Route::get('/i/info', function() {
   phpinfo();
});

Route::get('/', ['as' => 'home'], function () {
    return View::make('index');
});

if (Auth::check()) {
	Route::get('/', 'UsersController@self_feed');
}


Route::get('test', function() {
   return View::make('test')->with('title', 'Test title')->with('hasNavbar', 1);
});

Route::get('buy/{id}/create', 'BuyController@create');

Route::get('/@{username}', function($username) {
    return Redirect::to(action('UsersController@show', $username));
});
Route::get('/{username}', 'UsersController@show');


Route::controller('password', 'RemindersController');

Route::post('buy/{id}', 'BuyController@store');
Route::get('buy/{id}/confirm', 'BuyController@showConfirm');

Route::show('buy/{id}/confirm', 'BuyController@payment');
Route::any('buy/{id}/cancel', 'BuyController@cancel');

Route::resource('buy', 'BuyController', ['only' => ['create', 'store', 'show']]);
Route::resource('comments', 'CommentsController', ['only' => ['create', 'store', 'show', 'destroy']]);
Route::resource('self/user', 'UsersController', ['only' => ['create', 'store', 'show']]);
Route::resource('items', 'ItemsController');

Route::resource('session', 'SessionController', ['only' => ['create', 'store']]);
Route::get('login', 'SessionController@create');
Route::get('logout', 'SessionController@destroy');
Route::get('signup', 'UsersController@create');



