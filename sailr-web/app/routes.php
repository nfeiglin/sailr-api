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

Route::get('views/{zero}/{one?}/{two?}/{three?}/{four?}/{five?}/{six?}', function($zero, $one = null, $two = null, $three = null, $four = null, $five = null, $six = null){
    $args = func_get_args();
    $string = '';
    foreach($args as $key => $value) {
        $string = $string . '.' . $value;
    }

    $ipnModel = \LogicalGrape\PayPalIpnLaravel\Models\IpnOrder::findOrFail(7);

    $ipn = new \Sailr\Emporium\Merchant\Webhooks\PaypalWebhook($ipnModel);

    return View::make($string)->with('title', 'title')->with('ipn', $ipn);//->with('isPartial', 0);

});

Route::get('events/{zero}/{one?}/{two?}/{three?}/{four?}/{five?}/{six?}', function($zero, $one = null, $two = null, $three = null, $four = null, $five = null, $six = null){
    $args = func_get_args();
    $string = '';
    foreach($args as $key => $value) {

        if ($key == 0) {
            $string = $value;
        } else {
            $string = "$string.$value";
        }

    }

    $ipnModel = \LogicalGrape\PayPalIpnLaravel\Models\IpnOrder::findOrFail(7);


    $eventArray = array(
        'buyerID' => 11,
        'sellerID' => 12,
        'itemID' => 9,
        'ipn' => $ipnModel,
    );

    $eventArray = (object) $eventArray;

    //dd($eventArray);

    Event::fire($string, $eventArray);

    echo $string;
    echo '<br>FIRED';
});




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

View::composer('users.create', function($view){
    $view->with('purpleBG', true);
});

View::composer('password.remind', function($view){
    $view->with('purpleBG', true);
});

View::composer('password.reset', function($view){
    $view->with('purpleBG', true);
});

Route::get('/i/info', function () {
    //VIP::canPerformAction('product.create', User::findOrFail(11)

    $user = User::findOrFail(11);

    if ($user->canPerformActionOnPlan('product.create')) {
        return 'YESSS';
    }

    else {
        return 'NO!!';
    }
    //phpinfo();
});


if (Auth::check()) {
    Route::get('/', 'UsersController@self_feed');
} else {
    Route::get('/', function () {
        return View::make('index');
    });
}

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

    Route::resource('products', 'ItemsController', ['only' => ['create', 'store', 'show', 'edit', 'update']]);

    Route::group(['before' => 'guest'], function () {
        Route::resource('session', 'SessionController', ['only' => ['create', 'store']]);
        Route::resource('user/create', 'UsersController@create');
    });
    Route::get('session/destroy', 'SessionController@destroy');
    Route::controller('password', 'RemindersController');


    Route::get('/{username}/product/{id}', 'BuyController@create');


    Route::group(['before' => ['auth']], function () {

        Route::resource('{username}/profile_img', 'ProfileImageController');
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
        Route::resource('comments', 'CommentsController', ['only' => ['create', 'store', 'show', 'destroy']]);

        Route::get('items/create', 'ItemsController@create');
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





