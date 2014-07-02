<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class ProfileImg extends \Eloquent
{
    use SoftDeletingTrait;
    protected $dates = ['created_at', 'updated_at'];
    protected $fillable = ['user_id', 'type', 'url'];
    protected $hidden = ['created_at','updated_at', 'deleted_at', 'id', 'user_id'];
    protected $table = 'profileimgs';
    protected $softDelete = true;

    public function user()
    {
        return $this->belongsTo('User');
    }

    /*
     * Adding or removing elements in this array will break the ProfileImageController@destroy function
     */
    public static $photoSizes = array(
        'small' => ['size' => 150, 'quality' => 60],
        'medium' => ['size' => 256, 'quality' => 75],
        'large' => ['size' => 612, 'quality' => 80]
    );

    public static function resizeAndStoreUploadedImages($files, User $user)
    {


        $image = $files[0]; //Only expecting 1 image!
        foreach (ProfileImg::$photoSizes as $type => $sizeAndQuality) {
            $newPath = 'img/' . sha1(microtime()) . '.jpg';
            $encodedImage = Image::make($image->getRealPath());
            $encodedImage->fit($sizeAndQuality['size'], $sizeAndQuality['size']);
            $encodedImage->encode('jpg', $sizeAndQuality['quality']);
            $encodedImage->save($newPath);


            ProfileImg::create([
                'user_id' => $user->id,
                'type' => $type,
                'url' => asset($newPath)
            ]);

        }


        return true;
    }

    public static function setDefaultProfileImages(User $user)
    {

        $urls = ['default-sm', 'default-md', 'default-lg'];
        $counter = 0;

        foreach (ProfileImg::$photoSizes as $type => $sizeAndQuality) {
            $newPath = 'img/' . $urls[$counter] . '.jpg';
            ProfileImg::create([
                'user_id' => $user->id,
                'type' => $type,
                'url' => asset($newPath)
            ]);

            $counter++;
        }
    }
}