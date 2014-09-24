<?php



/* Webhook Routes === THESE ARE IMPORTANT !!! */
Route::post('purchase/{id}', 'BuyController@store', ['before' => 'auth']);
Route::post('payment/ipn', array('uses' => 'IpnController@store', 'as' => 'ipn'));
Route::post('payment/stripe-webhook', 'Laravel\Cashier\WebhookController@handleWebhook');

/* KEEP THE WEBHOOK ROUTES */

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

Route::get('/', function(){
    return Response::json('API Base URL');
});

/* User Routes */
Route::resource('users', 'UsersController');
Route::get('users/{id}/feed', 'FeedsController@show');
Route::get('users/{id}/following', 'UsersController@following');
Route::get('users/{id}/followers', 'UsersController@followers');

/* Collection Routes */
Route::resource('users/{id}/collections', 'CollectionsController');
Route::post('users/{id}/collections/favourite', 'CollectionsController@favourite');
Route::delete('collections/destroy/{collection_id}', 'CollectionsController@destroyCollection');
Route::delete('collections/destroy/{collection_id}/item/{item_id}', 'CollectionsController@destroyCollectionItem');

/* Items Routes */
Route::resource('items', 'ItemsController', ['only' => ['store', 'show', 'edit', 'update', 'index']]);
Route::put('items/{id}/toggle-visibility', 'ItemsController@toggleVisibility');
Route::post('items/{id}/toggle-visibility', 'ItemsController@toggleVisibility');

/* Photos Routes */
Route::post('photos/{item_id}', 'PhotosController@store');
Route::delete('photos/{item_id}/{set_id?}', 'PhotosController@destroy');

/* Profile Image Routes */
Route::resource('users/profile-image', 'ProfileImageController'); // (For the logged in user)

/* Comments Routes */
Route::resource('comments', 'CommentsController', ['only' => ['create', 'store', 'show', 'destroy']]);
Route::get('items/{items_id}/comments', 'CommentsController@item_comments');

/* Relationship Routes */
Route::resource('relationship', 'RelationshipsController');

/* Feeds Routes */
Route::resource('feeds', 'FeedsController');
Route::get('users/feeds/main', 'FeedsController@userFeed');

/* Searches Routes */
Route::get('/searches/{query}', 'SearchesController@show');

/* Notification Routes */
Route::resource('notifications', 'NotificationsController'); //This will likely get integrated into some sort of Feed

/* Sessions Routes */
Route::resource('sessions', 'SessionController', ['only' => ['create', 'store', 'destroy']]);
Route::get('login', 'SessionController@create');
Route::get('logout', 'SessionController@destroy');
Route::get('signup', 'UsersController@create');


/* MISC */
Route::group(['prefix' => 'onboard'], function(){
   Route::group(['prefix' => 'recent'], function() {
      Route::get('products/{offset?}/{limit?}', 'OnboardController@getRecentProducts');
   });
});






