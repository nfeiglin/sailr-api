<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent implements UserInterface, RemindableInterface
{
    protected $softDelete = true;

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
    public $hidden = array('password', 'created_at', 'updated_at', 'deleted_at', 'email', 'remember_token');

    public $guarded = array('id', 'created_at', 'updated_at');
    //protected $appends = ['counts'];

    public static $rules = array(
        'name' => 'required|min:2|max:99',
        'email' => 'required|email|max:99|unique:users,email',
        'username' => 'required|alpha_dash|max:99|unique:users,username',
        'password' => 'required|min:6',
        'bio' => 'sometimes|max:240'
    );

    public static $updateRules = array(
        'name' => 'sometimes|min:2|max:99',
        'email' => 'sometimes|email|max:99|unique:users,email',
        'username' => 'sometimes|alpha_num|min:2|max:99',
        'password' => 'sometimes|min:6',
        'bio' => 'sometimes|max:240'
    );

    public function getCountsAttribute() {
        $following = Relationship::where('user_id', '=', $this->getAuthIdentifier())->count();
        $followers = Relationship::where('follows_user_id', '=', $this->getAuthIdentifier())->count();

        return $this->attributes['counts'] = [
            'following' => $following,
            'followers' => $followers
        ];
    }


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

    public function getRememberToken()
    {
        return $this->remember_token;
    }

    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    public function getRememberTokenName()
    {
        return 'remember_token';
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

    public function comments(){
        return $this->hasMany('Comment');
    }

    public function whereUsername($username)
    {
        return $this->where('username', '=', $username)->firstOrFail();
    }

    public static function loginFailResponse() {
        $res = array(
            'meta' => array(
                'statuscode' => 401,
                'message' => 'Username, email, or password is incorrect'
            )
        );
        return Response::json($res, 401);
    }
}