<?php


namespace Sailr\Tags;


class SailrTagger extends BaseTagger {

    public function getTaggedUsers($string = '', $columns = null, $relationships = null) {
        $users = User::whereIn('username', $this->getTaggedUsers($string));

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