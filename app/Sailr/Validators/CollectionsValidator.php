<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 4/08/2014
 * Time: 8:40 PM
 */

namespace Sailr\Validators;


class CollectionsValidator extends BaseValidator implements ValidatorInterface {
    protected  $rules = [
        'create' => [
            'collection_title' => 'required|min:2|max:255',
            'item_id' => 'required'
        ],
        'update' => [
            'collection_id' => 'required',
            'item_id' => 'required'
        ]
    ];

} 