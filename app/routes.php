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

/*
Route::bind('user', function($value, $route)
{
    $repo = new Sailr\Repository\UsersRepository(new \User);
    $repo->make(['ProfileImg']);

    return $repo->getFirstOrFailBy('id', $value);
});
*/

Route::get('/', function(){
    return Response::json('API Base URL');
});

Route::resource('users', 'UsersController');
Route::get('users/{user}', 'UsersController@show');

Route::get('/searches/{query}', 'SearchesController@show');
Route::get('{username}/following', 'UsersController@following');
Route::get('{username}/followers', 'UsersController@followers');


    Route::resource('items', 'ItemsController', ['only' => ['store', 'show', 'edit', 'update', 'index']]);

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
    Route::get('api/collections/{username}/all', 'CollectionsController@index');



    Route::group(['before' => ['auth']], function () {

        Route::resource('settings/user/profile-image', 'ProfileImageController');
        Route::post('photo/upload/{item_id}', 'PhotosController@store');
        Route::delete('photo/{item_id}', 'PhotosController@destroy');
        Route::put('photo/{item_id}', 'PhotosController@destroy');


        Route::resource('relationship', 'RelationshipsController');

        Route::get('/{username}/product/{id}/comments', 'CommentsController@item_comments');
        Route::resource('comments', 'CommentsController', ['only' => ['create', 'store', 'show', 'destroy']]);


        Route::put('products/{id}/toggle-visibility', 'ItemsController@toggleVisibility');
        Route::post('products/{id}/toggle-visibility', 'ItemsController@toggleVisibility');
        Route::resource('notifications', 'NotificationsController');

        Route::post('collections/favourite', 'CollectionsController@favourite');
        Route::post('collections/store', 'CollectionsController@store');
        Route::delete('collections/destroy/{id}', 'CollectionsController@destroyCollection');
        Route::delete('collections/destroy/{collection_id}/item/{item_id}', 'CollectionsController@destroyCollectionItem');
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






