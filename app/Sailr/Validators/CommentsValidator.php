<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 4/08/2014
 * Time: 8:40 PM
 */

namespace Sailr\Validators;


class CommentsValidator extends BaseValidator implements ValidatorInterface {
    protected  $rules = [
        'create' => [
            'comment' => ['required', 'max:400'],
            'item_id' => ['required', 'exists:items,id']
        ]
    ];

} 