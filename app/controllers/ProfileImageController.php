<?php

use Sailr\Api\Responses\Responder;

class ProfileImageController extends \BaseController
{

    /**
     * @var Responder
     */
    protected $responder;

    public function __construct(Responder $responder)
    {
        $this->responder = $responder;
    }


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
            return $this->responder->errorMessageResponse("Could not upload Invalid image");
        }

        ProfileImg::where('user_id', '=', Auth::user()->id)->delete();

        ProfileImg::resizeAndStoreUploadedImages($files, Auth::user());

        $profileImg = ProfileImg::where('user_id', '=', Auth::user()->id)->where('type', 'medium')->get(['url', 'user_id']);
        $profileImg['object'] = 'profile_img';

        return $this->responder->createdModelResponse($profileImg);

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