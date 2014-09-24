<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Photo extends \Eloquent

{
    use SoftDeletingTrait;
    protected $dates = ['created_at', 'updated_at'];
    protected $fillable = ['user_id', 'item_id', 'type', 'url', 'set_id'];
    protected $hidden = ['item_id'];
    protected $softDelete = true;
    public static $setIDs = [];
    public static $thumbURLs = [];

    public function item()
    {
        return $this->belongsTo('Item');
    }

    public static function generateUniquePath()
    {
        $isUnique = false;
        do {
            $unique1 = uniqid('10510997103101', true);
            $unique2 = uniqid('5841', true);
            $directory = '/' . $unique2;
            $testdir = public_path() . '/' . $unique2;
            // print "DIR is: ";
            //  print $directory;
            if (!is_dir($testdir)) {
                $isUnique = true;
                mkdir($testdir);
            }
        } while (!$isUnique);

        return $testdir . '/' . $unique1 . '.jpg';
    }

    public static function validateImages($files) {
        $valid_mime_types = array(
            "image/gif",
            "image/png",
            "image/jpeg",
            "image/x-jpeg",
            "image/pjpeg",
            'image/x-jpeg2000-image',
            "image/x-png",
        );

        //Check that the uploaded file is actually an image (by MIME)
        foreach ($files as $file) {
            if (!in_array($file->getMimeType(), $valid_mime_types)) {
                return false;
            }
        }

        return true;
    }
    public static function resizeAndStoreUploadedImages ($files, Item $item) {
        $i = 0;
        $photoSizes = array(
            'full_res' => ['size' => 640, 'quality' => 80],
            'thumbnail' => ['size' => 150, 'quality' => 60]
        );

        foreach ($files as $image) {
            $set_id = uniqid(); //This id allows us to easily select the same photo in all its different sizes.

            foreach ($photoSizes as $type => $sizeAndQuality) {
                $newPath = 'img/' . sha1(microtime()) . '.jpg';
                $encodedImage = Image::make($image->getRealPath());
                $encodedImage->fit($sizeAndQuality['size'], $sizeAndQuality['size']);
                $encodedImage->encode('jpg', $sizeAndQuality['quality']);
                $encodedImage->save($newPath);

                $savedURL = asset($newPath);
                Photo::create([
                    'user_id' => $item->user_id,
                    'item_id' => $item->id,
                    'set_id' => $set_id,
                    'type' => $type,
                    'url' => $savedURL
                ]);


                if ($type == 'full_res') {
                    Photo::$thumbURLs[$i] = $savedURL;
                    Photo::$setIDs[$i] = $set_id;
                }


            }
            $i++;
        }

        return true;
    }

}
