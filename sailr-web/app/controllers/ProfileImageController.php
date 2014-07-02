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


        $files = Input::file('photos');
        if (!$files) {
            return Redirect::back()->with('fail', 'Please select a new image');
        }
        //dd($files);

        if(!is_array($files)) {
            $files = [$files];
        }

        //dd($files);
        //dd($files);
        //$files = Input::file('photos');
        //print_r($files);
        //dd($files);

        //Response::make('', 503);
        //dd($files);
        //Log::debug(print_r($files, 1));

        if(!Photo::validateImages($files)) {
            $data = ['message' => 'Invalid image'];
            if (Request::ajax()) {
                return Response::json(['message' => 'Invalid image'], 400);
            }

            else {
                return Redirect::back()->with('fail', 'Invalid photo');
            }

        }

        ProfileImg::where('user_id', '=', Auth::user()->id)->delete();

        ProfileImg::resizeAndStoreUploadedImages($files, Auth::user());

        $imagesArray = ProfileImg::where('user_id', '=', Auth::user()->id)->get(['type', 'url'])->toArray();

        if (Request::ajax()) {
            return Response::json(['message'=> 'Successfully uploaded', 'profile_img' => $imagesArray]);

        }
        else {
            return Redirect::back()->with('success', 'New profile photo set');
        }

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