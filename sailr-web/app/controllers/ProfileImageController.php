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
        $files = Request::file('photos');
        if(!is_array($files)) {
            $files = [$files];
        }
        //dd($files);
        //$files = Input::file('photos');
        //print_r($files);
        //dd($files);

        //Response::make('', 503);
        //dd($files);
        //Log::debug(print_r($files, 1));

        if(!Photo::validateImages($files)) {
            return Response::json(['message' => 'Invalid image'], 400);
        }

        ProfileImg::where('user_id', '=', Auth::user()->id)->delete();

        ProfileImg::resizeAndStoreUploadedImages($files, Auth::user());

        $imagesArray = ProfileImg::where('user_id', '=', Auth::user()->id)->get(['type', 'url'])->toArray();
        return Response::json(['message'=> 'Successfully uploaded', 'profile_img' => $imagesArray]);

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