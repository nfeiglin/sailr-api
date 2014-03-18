<?php

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

Event::listen('illuminate.query', function($query, $params, $time, $conn)
{
   print_r(array($query, $params, $time, $conn));
    print('End of Queary <br>');
});
Route::get('/', function()
{
	return View::make('hello');
});

Route::resource('items', 'ItemsController');
//Route::resource('user', 'UsersController');

Route::get('user/{id}', function($id) {
 $user = User::findOrFail($id);
 $res = $user->toArray();

    $following = Relationship::where('user_id', '=', $id)->count();
    $followers = Relationship::where('follows_user_id', '=', $id)->count();
   $res['counts'] = [
       'following' => $following,
       'followers' => $followers
   ];
    return Response::json($res);
});

Route::get('user/{id}/stream', function($id) {
   $user = User::findOrFail($id);
    $following = Relationship::where('user_id', '=', $user->id)->get(array('follows_user_id'));
    $following = $following->toArray();
//dd($following);

    $arrayOne = array();

    $counter = 0;
    foreach($following as $key => $value) {
        $arrayOne[$counter] = $value['follows_user_id'];
        $counter = $counter + 1;
    }

  $items = Item::whereIn('user_id', $arrayOne)->get()->toArray();


  return Response::json($items);
});