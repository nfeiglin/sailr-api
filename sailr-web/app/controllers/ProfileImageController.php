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
        if(!Photo::validateImages($files)) {
            return Redirect::back()->with('fail', 'Invalid image');
        }

        ProfileImg::resizeAndStoreUploadedImages($files, Auth::user());

        return Redirect::back()->with('success', 'New profile image successfully added');

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

        return Redirect::back()->with('success', 'Profile image successfully removed');

    }


}