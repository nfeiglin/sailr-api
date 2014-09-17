<?php namespace Sailr\Api\Support;
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 11/09/2014
 * Time: 8:11 PM
 */

use Illuminate\Support\Collection;

class ErrorCollection {

    /**
     * @var string
     */
    protected $message = '';

    /**
     * @var array
     */
    protected $errors = [];


    function __construct($message, $errors)
    {
        $this->message = $message;
        $this->errors = $errors;
    }


    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }



} 