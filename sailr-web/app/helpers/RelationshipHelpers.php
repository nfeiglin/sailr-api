<?php
/**
 * User: Nathan
 * Date: 23/03/14
 * Time: 4:00 PM
 */

use Illuminate\Auth\UserInterface;
class RelationshipHelpers
{
    public static function follows_you(UserInterface $user)
    {
        $doesExist = DB::table('relationships')->where('user_id', '=', $user->id)->where('follows_user_id', '=', Auth::user()->id)->count();
        if ($doesExist) {
            return true;
        }
        return false;
    }

    public static function you_follow(UserInterface $user)
    {
        $doesExist = DB::table('relationships')->where('user_id', '=', Auth::user()->id)->where('follows_user_id', '=', $user->id)->count();
        if ($doesExist) {
            return true;
        }
        return false;
    }

    public static function get_follows_user(UserInterface $user)
    {
        $followers = Relationship::where('follows_user_id', '=', $user->id)->orderBy('created_at', 'dsc')->get(array('user_id'))->toArray();

        $arrayOne = array();
        $counter = 0;
        foreach ($followers as $key => $value) {
            $arrayOne[$counter] = $value['user_id'];
            $counter = $counter + 1;
        }

        $users = User::whereIn('id', $arrayOne)->with(array(
            'ProfileImg' => function ($y) {
                    $y->select(['id', 'type', 'url']);
                }
        ))->get()->toArray();

        return $users;
    }

    public static function get_user_following(UserInterface $user)
    {
        $following = Relationship::where('user_id', '=', $user->id)->orderBy('created_at', 'dsc')->get(array('follows_user_id'))->toArray();

        $arrayOne = array();
        $counter = 0;
        foreach ($following as $key => $value) {
            $arrayOne[$counter] = $value['follows_user_id'];
            $counter = $counter + 1;
        }

        $users = User::whereIn('id', $arrayOne)->with(array(
            'ProfileImg' => function ($y) {
                    $y->select(['id', 'type', 'url']);
                }
        ))->get()->toArray();

        return $users;
    }

    public static function count_follows_user(UserInterface $user)
    {
        $followers = Relationship::where('follows_user_id', '=', $user->id)->count();

        return $followers;
    }

    public static function count_user_following(UserInterface $user)
    {
        $following = Relationship::where('user_id', '=', $user->id)->count();

        return $following;
    }


    /*
    public static function findSuggestedFriends()
    {
        $db = DB::table('users')->select('id')->join('relationships', function($join) {
           $join->on('relationships.user_id', '=', 'users.id');
        })->join('relationships.follows_user_id', function($join1){
                $join1->on('relationships.follows_user_id', 'relationships.user_id', '=', 'relationships.user_id', 'relationships.follows_user_id');
            })->join('users', 'id', '=', 'relationships.follows_user_id');

        print $db->toSql();

        print json_encode($db->get());
    }

    */
}