<?php




Route::post('purchase/{id}', 'BuyController@store', ['before' => 'auth']);
Route::post('payment/ipn', array('uses' => 'IpnController@store', 'as' => 'ipn'));
Route::post('payment/stripe-webhook', 'Laravel\Cashier\WebhookController@handleWebhook');

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

//App::register('Sailr\TestPipe\TestPipeServiceProvider')
//Route::get('asset/show/{path?}', 'Sailr\TestPipe\TestPipeController@showAsset')->where('path', '(.*)');
//\Sailr\TestPipe\TestPipe::make()->tags('css')

Route::get('/', function(){
    return Response::json('hhhh');
});
Route::resource('users', 'UsersController');

Route::get('/s/{query}', 'SearchesController@show');

Route::group(['before' => 'csrf'], function () {

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
    
    Route::resource('{username}/collections', 'CollectionsController');
    Route::get('{username}/collections', 'CollectionsController@index');
    Route::get('{username}/collections/{id}', 'CollectionsController@show');
    Route::get('api/collections/{username}/all', 'CollectionsApiController@index');



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

        Route::delete('relationship', 'RelationshipsController@destroy');
        Route::resource('relationship', 'RelationshipsController');

        Route::get('/{username}/product/{id}/comments', 'CommentsController@item_comments');
        Route::resource('comments', 'CommentsController', ['only' => ['create', 'store', 'show', 'destroy']]);

        Route::get('dashboard/products', 'ItemsController@index');

        Route::put('products/{id}/toggle-visibility', 'ItemsController@toggleVisibility');
        Route::post('products/{id}/toggle-visibility', 'ItemsController@toggleVisibility');
        Route::resource('me/notifications', 'NotificationsController');
        Route::get('me/notification/{id}', 'NotificationsController@show');

        Route::post('api/collections/favourite', 'CollectionsApiController@favourite');
        Route::post('api/collections/store', 'CollectionsApiController@store');
        Route::delete('api/collections/destroy/{id}', 'CollectionsApiController@destroyCollection');
        Route::delete('api/collections/destroy/{collection_id}/item/{item_id}', 'CollectionsApiController@destroyCollectionItem');
    });


    Route::group(array('prefix' => 'self'), function () {
        Route::get('login', 'SessionController@create');
        Route::get('logout', 'SessionController@destroy');
        Route::get('signup', 'UsersController@create');

        Route::resource('user', 'UsersController', ['only' => ['store', 'show']]);

    });

    Route::group(['prefix' => 'onboard'], function(){
       Route::group(['prefix' => 'recent'], function() {
          Route::get('products/{offset?}/{limit?}', 'OnboardController@getRecentProducts');
       });
    });


});





