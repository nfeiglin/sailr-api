<?php

class ProfileImageController extends \BaseController
{

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $files = Request::instance()->files->get('photos');
        $p = Photo::validateImages($files);

            return Redirect::back()->with('errror', 'Invalid image');

        ProfileImg::resizeAndStoreUploadedImages($files, Auth::user());

        $res = array(
            'meta' => array(
                'statuscode' => 201,
                'message' => 'New profile image successfully added'
            ),

            'data' => ProfileImg::where('user_id', '=', Auth::user()->id)->get()->toArray()
        );
        return Response::json($res, 201);

    }


    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy()
    {
        $images = ProfileImg::where('user_id', '=', Auth::user()->id)->delete();
        ProfileImg::setDefaultProfileImages(Auth::user());

        $res = array(
            'meta' => array(
                'statuscode' => 200,
                'message' => 'Profile image successfully removed'
            ),

        );
        return Response::json($res, 200);

    }


}