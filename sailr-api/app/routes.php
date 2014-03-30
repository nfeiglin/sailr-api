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

Event::listen('illuminate.query', function ($query, $params, $time, $conn) {
    print_r(array($query, $params, $time, $conn));
    print('End of Queary <br>');
});

Route::get('/', function () {
    return View::make('hello');
});


Route::get('login', function () {
    return "login.create";
});

Route::post('login', 'AuthController@store');

Route::group(array('prefix' => 'api'), function () {
    // Route::resource('auth', 'AuthController', array('only' => array('store', 'destroy')));

    /*
    Route::post('image', function(){
        print "first";
       // $photos = Request::instance()->files->getIterator();
        //dd($photos, 1);
        print "got input files";
        print_r($_FILES['photos']);
        foreach($_FILES['photos'] as $photo) {
            print "Photo upload loop";

            $path = 'img/' . sha1(microtime()) . '.jpg';
            /*
            $bigPhoto = Image::make($photoOriginal);

            $bigPhoto->resize(612, 612, false);
            $bigPhoto->encode('jpg', 70);
            $bigPhoto->save($path);
            $bigPhoto = $bigPhoto->destroy();

            $path = 'img/' . sha1(microtime()) . '.jpg';
            $thumbnail = Image::make($photoOriginal)->resize(150, 150, false)->encode('jpg', 60);

                $thumbnail->save($path);
            $thumbnail = $thumbnail->destroy();
*/
            //$photo->getRealPath();
    /*
        $imagine = new \Imagine\Gd\Imagine();
            $image = $imagine->open($photo['tmp_name']);
            $box = new \Imagine\Image\Box(612, 612);
            $image->resize($box);
            $image->save($path);
            print "End photo loop";

            $path = "";
            $photoOriginal = "";

        }


    });
*/
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
