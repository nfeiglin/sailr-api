<?php


namespace Sailr\Tags;
use User;

class SailrTagger extends BaseTagger {

    /**
     * @param string $string
     * @param array $columns
     * @param array $relationships
     * @return \Illuminate\Support\Collection
     */

    public function getTaggedUsers($string = '', $columns = [], $relationships = []) {

        $usernames = $this->getTaggedUserNames($string);

        if (!$usernames) {
            //Dont waste and sql query and just return a blank array

            return [];
        }
        $users = User::whereIn('username', $usernames);

        if($relationships) {
            $users = $users->with($relationships);
        }

        if ($columns) {
            $users = $users->get($columns);
        }

        else {
            $users = $users->get();
        }

        return $users;
    }
} 