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
        /*
    *
    * TODO $user_id = Auth::user()->id;
   */

        $user_id = 3;
        $input = Input::all();
        $photos = Request::instance()->files->all(array('photos'));
        print_r($photos);
        // die();

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
        $photoTypes = array(
            'full_res' => [612, 75],
            'thumbnail' => [150, 60]
        );

        foreach ($photos as $photo) {
            $originalPhoto = $photo;

            foreach ($photoTypes as $type => $size) {
                print "Photo upload loop";
                $photo = $originalPhoto->getRealPath();
                $path = Photo::generateUniquePath();
                $photo = Image::make($photo);
                $photo->resize($size[0], $size[0], false);
                $photo->encode('jpg', $size[1]);
                $photo->save($path);

                $photoDB = new Photo();
                $photoDB->user_id = $user_id;
                $photoDB->item_id = $item->id;
                $photoDB->type = $type;
                $photoDB->url = $path;
                $photoDB->save();
            }
        }
        $res = array(
            'meta' => array(
                'statuscode' => 200,
                'message' => 'Item successfully posted!'
            )
        );
        return Response::json($res);

    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        return View::make('items.show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        return View::make('items.edit');
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
        //
    }

}
