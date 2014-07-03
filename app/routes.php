<?php
use Intervention\Image\Facades;

    Route::post('purchase/{id}', 'BuyController@store', ['before' => 'auth']);
    //Testing
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


    if (Auth::check()) {
        if (!array_key_exists('unread_notifications_count', $view->getData())) {
            $count = Notification::where('user_id', '=', Auth::user()->id)->where('viewed', '=', false)->count();
            $view->with('unread_notifications_count', $count);
        }


    }
});

View::composer('index', function($view){
    $view->with('purpleBG', false);
});

View::composer('*', function(Illuminate\View\View $view) {
    /* Enclosing this in a try / catch because user may not be logged in! */
    try {
        $loggedInUser = User::where('id', '=', Auth::user()->id)->with(
            ['ProfileImg' => function($p) {
                $p->where('type', '=', 'small');
            }])->firstOrFail(['id', 'name', 'username'])->toJson();
    }

    catch (Exception $e) {
        $loggedInUser = 'false';
    }

   $view->with('loggedInUser', $loggedInUser);
});

View::composer('users.create', function($view){
    $view->with('purpleBG', true);
});

View::composer('users.show', function($view){
    $view->with('purpleBG', true);
});

View::composer('password.remind', function($view){
    $view->with('purpleBG', true);
});

View::composer('password.reset', function($view){
    $view->with('purpleBG', true);
});


if (Auth::check()) {
    Route::get('/', 'UsersController@self_feed');

    Route::get('/i/homepage', function () {
        return View::make('index');
    });
} else {
    Route::get('/', function () {
        return View::make('index');
    });
}

Route::get('support/contact', ['as' => 'contact', function() {
    return View::make('support.contact')->with('title', "We're here for you");
}]);

Route::group(['prefix' => 'legal'], function() {
    Route::get('terms', ['as' => 'termsOfService', function() {
       return View::make('legal.termsOfService');
    }]);

    Route::get('privacy-policy', ['as' => 'privacyPolicy', function() {
        return View::make('legal.privacyPolicy');
    }]);

});

Route::get('/s/{query}', 'SearchesController@show');
Route::post('payment/ipn', array('uses' => 'IpnController@store', 'as' => 'ipn'));
Route::post('payment/stripe/webhook', 'Laravel\Cashier\WebhookController@handleWebhook');

Route::group(['before' => 'csrf'], function () {
    Route::get('test', function () {
        return View::make('test')->with('title', 'Test title')->with('hasNavbar', 1);
    });


    Route::get('/@{username}', function ($username) {
        return Redirect::to(action('UsersController@show', $username));
    });
    Route::get('/{username}', 'UsersController@show');
    Route::get('{username}/following', 'UsersController@following');
    Route::get('{username}/followers', 'UsersController@followers');

    Route::resource('purchase', 'BuyController', ['only' => ['store', 'show']]);

    Route::resource('products', 'ItemsController', ['only' => ['store', 'show', 'edit', 'update']]);

    Route::group(['before' => 'guest'], function () {
        Route::resource('session', 'SessionController', ['only' => ['create', 'store']]);
        Route::resource('user/create', 'UsersController@create');
    });
    Route::get('session/destroy', 'SessionController@destroy');
    Route::controller('password', 'RemindersController');


    Route::get('/{username}/product/{id}', 'BuyController@create');


    Route::group(['before' => ['auth']], function () {

        Route::resource('settings/user/profile-image', 'ProfileImageController');
        Route::post('photo/upload/{item_id}', 'PhotosController@store');
        Route::delete('photo/{item_id}', 'PhotosController@destroy');
        Route::put('photo/{item_id}', 'PhotosController@destroy');

        Route::post('buy/{id}', 'BuyController@store');
        Route::any('buy/{id}/cancel', 'BuyController@cancel');
        Route::get('buy/{id}/confirm', 'BuyController@showConfirm');
        Route::post('buy/{id}/confirm', 'BuyController@doConfirm');

        Route::group(['prefix' => 'settings'], function() {
            Route::get('account', 'SettingsController@getAccount');
            Route::put('account', 'SettingsController@putAccount');
            Route::resource('subscription', 'SubscriptionsController', ['only' => ['index', 'destroy', 'store']]);
            Route::post('subscription/delete', 'SubscriptionsController@destroy');
            //Route::delete('subscription/delete', 'SubscriptionsController@destroy');

            Route::group(['before' => ['subscribed']], function() {
                Route::resource('billing', 'BillingsController');
                Route::put('billing', 'BillingsController@update');
            });

        });

        Route::get('plans/choose', ['before' => 'not-subscribed', 'as' => 'choose-plan', function() {
            return View::make('subscriptions.pick')->with('title', 'Welcome to Sailr | Choose a plan');
        }]);



        Route::delete('relationship', 'RelationshipsController@destroy');
        Route::resource('relationship', 'RelationshipsController');

        Route::get('/{username}/product/{id}/comments', 'CommentsController@item_comments');
        Route::resource('comments', 'CommentsController', ['only' => ['create', 'store', 'show', 'destroy']]);

        Route::get('dashboard/products', 'ItemsController@index');

        Route::put('products/{id}/toggle-visibility', 'ItemsController@toggleVisibility');
        Route::post('products/{id}/toggle-visibility', 'ItemsController@toggleVisibility');
        Route::resource('me/notifications', 'NotificationsController');
        Route::get('me/notification/{id}', 'NotificationsController@show');
    });


    Route::group(array('prefix' => 'self'), function () {
        Route::get('login', 'SessionController@create');
        Route::get('logout', 'SessionController@destroy');
        Route::get('signup', 'UsersController@create');

        Route::resource('user', 'UsersController', ['only' => ['store', 'show']]);

    });
});





