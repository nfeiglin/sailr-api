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

View::composer('*', function ($view) {
    if (!array_key_exists('hasNavbar', $view->getData())) {
        $view->with('hasNavbar', 1);
    }
});

Route::get('i/i/i', function() {
    return Holystone::sanitize('<blockquote>yoyoyoyo</blockquote> <a href="http://google.com">test</a>  http://facebook.com');
});
Route::get('/i/info', function () {
    phpinfo();
});


if (Auth::check()) {
    Route::get('/', 'UsersController@self_feed');
} else {
    Route::get('/', function () {
        return View::make('index');
    });
}

Route::group(['before' => 'csrf'], function () {
    Route::get('test', function () {
        return View::make('test')->with('title', 'Test title')->with('hasNavbar', 1);
    });

    Route::get('buy/{id}/create', 'BuyController@create');

    Route::get('/@{username}', function ($username) {
        return Redirect::to(action('UsersController@show', $username));
    });
    Route::get('/{username}', 'UsersController@show');
    Route::get('{username}/following', 'UsersController@following');
    Route::get('{username}/followers', 'UsersController@followers');

    Route::get('item/show/{id}', 'BuyController@create');
    Route::resource('buy', 'BuyController', ['only' => ['create', 'store', 'show']]);
    Route::resource('items', 'ItemsController');

    Route::group(['before' => 'guest'], function () {
        Route::resource('session', 'SessionController', ['only' => ['create', 'store']]);
        Route::resource('user/create', 'UsersController@create');
    });
    Route::get('session/destroy', 'SessionController@destroy');
    Route::controller('password', 'RemindersController');


    Route::get('/item/{id}', 'BuyController@create');


    Route::group(['before' => ['auth']], function () {
        Route::resource('{username}/profile_img', 'ProfileImageController');
        Route::post('buy/{id}', 'BuyController@store');
        Route::any('buy/{id}/cancel', 'BuyController@cancel');
        Route::get('buy/{id}/confirm', 'BuyController@showConfirm');
        Route::post('buy/{id}/confirm', 'BuyController@doConfirm');
        Route::controller('settings', 'SettingsController');
        Route::delete('relationship', 'RelationshipsController@destroy');
        Route::resource('relationship', 'RelationshipsController');
        Route::resource('comments', 'CommentsController', ['only' => ['create', 'store', 'show', 'destroy']]);

        Route::get('items/create', 'ItemsController@create');
        Route::get('dashboard/products', 'ItemsController@index');
    });


    Route::group(array('prefix' => 'self'), function () {
        Route::get('login', 'SessionController@create');
        Route::get('logout', 'SessionController@destroy');
        Route::get('signup', 'UsersController@create');

        Route::resource('user', 'UsersController', ['only' => ['store', 'show']]);

    });
});





