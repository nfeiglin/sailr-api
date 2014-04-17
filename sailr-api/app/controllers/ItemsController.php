<?php

class ItemsController extends BaseController
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return Response::json(Item::all());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $user_id = Auth::user()->id;

        $input = Input::all();
        $files = Request::instance()->files->get('photos');
        $validator = Validator::make($input, Item::$rules);

        if ($validator->fails()) {
            $res = array(
                'meta' => array(
                    'statuscode' => 400,
                    'message' => 'Invalid data',
                    'errors' => $validator->messages()->all()
                )
            );
            return Response::json($res, 400);
        }

        /* Running HTML entities over the text input to minimise risk of XSS */
        $input['title'] = e($input['title']);
        $input['description'] = e($input['description']);

        $item = $this->doItemCreationFromInput($input, $user_id);

        $p = Photo::validateImages($files);

        if (!$p) {
            $res = array(
                'meta' => array(
                    'statuscode' => 415,
                    'message' => 'Invalid images',
                    'errors' => ['Your images are an invalid format']
                )
            );
            return Response::json($res, 415);
        }
        Photo::resizeAndStoreUploadedImages($files, $item);

        $res = array(
            'meta' => array(
                'statuscode' => 201,
                'message' => 'Item successfully posted!'
            ),

            'data' => $item->toArray()
        );
        return Response::json($res, 201);

    }

    /**
     * Display the specified resource.
     *
     * @param  string $username
     * @param int $item_id
     * @return Response
     */
    public function show($username, $item_id)
    {
        /*
         * Find the item listing from the DB
         */
        $user = User::where('username', '=', $username)->firstOrFail(array('id', 'username', 'name', 'bio'))->toArray();
        $items = Item::with(array(
            'Photos' => function ($y) {
                    $y->select(['item_id', 'type', 'url']);
                },
        ))->where('id', '=', $item_id)->where('user_id', '=', $user['id'])->firstOrFail()->toArray();

        /*
         * Generate and output the JSON response
         */
        $items['user'] = $user;
        $res = array(
            'meta' => array(
                'statuscode' => 200
            ),

            'data' => $items
        );

        return Response::json($res);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        if (!$item->user_id == Auth::user()->id) {
            $res = array(
                'meta' => array(
                    'statuscode' => 401,
                    'message' => 'Not authorised to delete that post'
                )
            );

            return Response::json($res, 401);
        }

        $photos = Photo::where('item_id', '=', $item->id);
        $photos->delete();
        $item->delete();

        /*
         * TODO: When calling these from buyer's panel, use "whereTrashed"
         */

    }

    public function doItemCreationFromInput(array $input, $user_id)
    {

        $item = new Item();
        $item->user_id = $user_id;
        $item->title = $input['title'];
        $item->description = $input['description'];
        $item->price = $input['price'];
        $item->currency = $input['currency'];
        $item->initial_units = $input['initial_units'];

        $item->save();
        return $item;
    }

}
