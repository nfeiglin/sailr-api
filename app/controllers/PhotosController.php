<?php

use Sailr\Validators\PhotosValidator;
use Sailr\Api\Responses\Responder;

class PhotosController extends \BaseController
{

    /**
     * @var PhotosValidator
     * @var Responder
     */
    protected $photosValidator;
    protected $responder;

    public function __construct(PhotosValidator $validator, Responder $responder) {
        $this->photosValidator = $validator;
        $this->responder = $responder;
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

        if (!is_array($input)) {
            $input = Input::all();
        }
        $item = Item::where('user_id', '=', Auth::user()->id)->where('id', '=', $item_id)->firstOrFail(['id', 'title', 'user_id']);
        $files = Input::file($filePostedKeyName);

        $validationData = ['photo' => $files];
        $this->photosValidator->validate($validationData, 'create');


        $filesArray = $files;

        if (!is_array($files)) {
            $filesArray = [$files];
        }


        $p = Photo::resizeAndStoreUploadedImages($filesArray, $item);

        if($p) {
            $setID = Photo::$setIDs[0];
            $thumbnailURL = Photo::$thumbURLs[0];


            $photo = new Photo();
            $photo['set_id'] = $setID;
            $photo['url'] = $thumbnailURL;


            return $this->responder->createdModelResponse($photo);
        }
        return $this->responder->errorMessageResponse(Lang::get('photo.error-upload'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $item_id
     * @return Response
     */
    public function destroy($item_id)
    {
        //Validate that there is at least one image

        $itemChanged = false;

        $photos = Photo::where('item_id', '=', $item_id)->where('user_id', '=', Auth::user()->id)->where('set_id', '=', (string) Input::get('set_id'))->delete();
        $numberOfPhotos = Photo::where('item_id', '=', $item_id)->count();

        if (!$numberOfPhotos > 0) {
            $item = Item::findOrFail($item_id);
            $item->public = 0;
            $item->save();

            $itemChanged = true;
        }

        if ($itemChanged) {
            return $this->responder->errorMessageResponse(Lang::get('photo.no-photos'));
        } else {
            return $this->responder->noContentSuccess();
        }



    }

}