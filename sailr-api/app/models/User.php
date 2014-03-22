<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent implements UserInterface, RemindableInterface
{
    public static $rules = array(
        'name' => 'required|min:2|max:99|alphadash',
        'email' => 'required|email|max:99|unique:users, email',
        'username' => 'required|alphanum|max:99|unique:users, username',
        'password' => 'required|min:6'
    );
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    public $hidden = array('password', 'created_at', 'updated_at', 'deleted_at');

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the e-mail address where password reminders are sent.
     *
     * @return string
     */
    public function getReminderEmail()
    {
        return $this->email;
    }

    public function items()
    {
        return $this->hasMany('Item');
    }

    public function following()
    {
        return $this->hasMany('Relationship', 'user_id');
    }

    public function relationship()
    {
        return $this->hasMany('Relationship', 'user_id');
    }

    public function followers()
    {
        return $this->hasMany('Relationship', 'follows_user_id');
    }

    public function ProfileImg()
    {
        return $this->hasMany('ProfileImg');
    }

    public static function Authenticate($credentials)
    {
        if (Auth::attempt(array(
            'username' => $credentials['username'],
            'password' => $credentials['password']
        ), true)
        ) {
            return true;
        } elseif (Auth::attempt(array(
            'email' => $credentials['email'],
            'password' => $credentials['password']
        ), true)
        ) {
            return true;
        } else {
            return false;
        }
    }

}