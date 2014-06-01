<?php

class PhotosController extends \BaseController
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * @params int $iteminteger the Item ID
     * @return Response
     */
    public function store($item_id)
    {

        $filePostedKeyName = 'file';

        $input = Input::json()->all();
        $headers = Request::header();

        if (!is_array($input)) {
            $input = Input::all();
        }
        $item = Item::where('user_id', '=', Auth::user()->id)->where('id', '=', $item_id)->firstOrFail(['id', 'title', 'user_id']);
        $files = Input::file($filePostedKeyName);

        $v = Validator::make(['photo' => $files], ['photo' => 'image|max:7168']); //7MB
        if ($v->fails()) {
            return Response::json('Your file is too large.', 400);

        }
        $filesArray = $files;

        if (!is_array($files)) {
            $filesArray = [$files];
        }


        $p = Photo::resizeAndStoreUploadedImages($filesArray, $item);

        if($p) {
            $setID = Photo::$setIDs[0];
            $thumbailURL = Photo::$thumbURLs[0];

            $res = ['set_id' => $setID, 'url' => $thumbailURL];
            return Response::json($res, 201);
        }
        return Response::json('There was an error uploading the photo', 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        //
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
     * @param  int $item_id
     * @return Response
     */
    public function destroy($item_id)
    {
        $photos = Photo::where('item_id', '=', $item_id)->where('user_id', '=', Auth::user()->id)->where('set_id', '=', (string) Input::get('set_id'))->delete();

        return Response::make(null,204);

    }

}