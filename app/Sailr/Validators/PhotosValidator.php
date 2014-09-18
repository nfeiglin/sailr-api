<?php
/**
 * Created by PhpStorm.
 * User: feiglin nathan
 * Date: 17/09/14
 * Time: 12:02 PM
 */

namespace Sailr\Validators;


class PhotosValidator extends BaseValidator implements ValidatorInterface {
    protected $rules = [
        'create' => [
            'photo' => 'image|max:7168'
        ],


    ];
} 