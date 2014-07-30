<?php


namespace Sailr\Tags;

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
            $users->with($relationships);
        }

        if ($columns) {
            $users->get($columns);
        }

        else {
            $users->get();
        }

        return $users;
    }
} 