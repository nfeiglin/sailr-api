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

Event::listen('illuminate.query', function ($query, $params, $time, $conn) {
    print_r(array($query, $params, $time, $conn));
    print('End of Queary <br>');
});
Route::get('/', function () {
    return View::make('hello');
});

//Route::resource('items', 'ItemsController');
//Route::resource('user', 'UsersController');

Route::post('item', function () {
    $user_id = Auth::user()->id;
    $input = Input::all();
    $photos = Input::file('photos');

    $validator = Validator::make($input, Item::$rules);

    if ($validator->fails()) {
        return Response::json($validator->messages()->all(), 400);
    }

    $item = new Item();
    $item->user_id = $user_id;
    $item->title = $input['title'];
    $item->description = $input['description'];
    $item->price = $input['price'];
    $item->currency = $input['currency'];
    $item->initial_units = $input['initial_units'];

    $item->save();

    /*
     *
     * TODO: Add image validation!
     */
    $photoTypes = array('full_res' => [612, 75], 'thumbnail' => [150, 60]);

    foreach ($photos as $photo) {
        $originalPhoto = $photo;

        foreach ($photoTypes as $type => $size) {
            $photo = $originalPhoto->getRealPath();
            $path = Photo::generateUniquePath();
            $photo = Image::make($photo)->resize($size[0], $size[0], false)->encode('jpg', $size[1]);
            $photo->save($path);

            $photoDB = new Photo();
            $photoDB->user_id = $user_id;
            $photoDB->item_id = $item->id;
            $photoDB->type = $type;
            $photoDB->url = asset($path);
            $photoDB->save();
        }
    }

});


Route::get('user/{id}', function ($id) {
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

Route::get('user/{id}/stream/', function ($id) {
    $user = User::findOrFail($id);
    //$user = Auth::user();
    $following = Relationship::where('user_id', '=', $user->id)->get(array('follows_user_id'));
    $following = $following->toArray();
//dd($following);

    $arrayOne = array();

    $counter = 0;
    foreach ($following as $key => $value) {
        $arrayOne[$counter] = $value['follows_user_id'];
        $counter = $counter + 1;
    }

    $items = Item::whereIn('user_id', $arrayOne)->with('User')->orderBy('created_at', 'dsc')->get()->toArray();


    return Response::json($items);
});