<?php

class RelationshipsController extends \BaseController
{

    /**
     * Store a newly created resource in storage.
     *
     * @return Response::json
     */
    public function store()
    {
        $input = Input::all();
        $user = Auth::user();
        if (array_key_exists('username', $input)) {
            $followUser = User::where('username', '=', $input['username'])->firstOrFail(array('id', 'name', 'username'));
        } else if (array_key_exists('user_id', $input)) {
            $followUser = User::where('id', '=', $input['user_id'])->firstOrFail(array('id', 'name', 'username'));
        } else {
            $res = array(
                'meta' => array(
                    'statuscode' => 400,
                    'message' => 'Correct identification for user to follow was not provided'
                )
            );

            return Response::json($res, 400);
        }

        $doesExist = DB::table('relationships')->where('user_id', '=', $user->id)->where('follows_user_id', '=', $followUser->id)->count();

        if ($doesExist | $followUser->id == $user->id) {
            $res = array(
                'meta' => array(
                    'statuscode' => 403,
                    'message' => 'You already follow ' . $followUser->username
                )
            );
            return Response::json($res, 403);
        }

        $relationship = new Relationship();
        $relationship->user_id = $user->id;
        $relationship->follows_user_id = $followUser->id;

        $relationship->save();

        $res = array(
            'meta' => array(
                'statuscode' => 201,
                'message' => 'Successfully followed ' . $followUser->username
            )
        );

        return Response::json($res, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     *
     * @return Response
     */
    public function destroy()
    {
        /*
         * Example POST DATA:
         * {
         *      "username": "MZ"
         * }
         */
        $input = Input::all();
        $user = Auth::user();

        if (array_key_exists('username', $input)) {
            $followUser = User::where('username', '=', $input['username'])->firstOrFail(array('id', 'name', 'username'));
        } else if (array_key_exists('user_id', $input)) {
            $followUser = User::where('id', '=', $input['user_id'])->firstOrFail(array('id', 'name', 'username'));
        } else {
            $res = array(
                'meta' => array(
                    'statuscode' => 400,
                    'message' => 'Correct identification for user to unfollow was not provided'
                )
            );

            return Response::json($res, 400);
        }

        $doesExist = DB::table('relationships')->where('user_id', '=', $user->id)->where('follows_user_id', '=', $followUser->id)->count();

        if (!$doesExist) {
            $res = array(
                'meta' => array(
                    'statuscode' => 403,
                    'message' => "You don't follow" . $followUser->username . " so you can't unfollow them"
                )
            );
            return Response::json($res, 403);
        }

        $relationship = Relationship::where('user_id', '=', $user->id)->where('follows_user_id', '=', $followUser->id);
        $relationship->delete();
        $res = array(
            'meta' => array(
                'statuscode' => 200,
                'message' => 'Successfully unfollowed ' . $followUser->username
            )
        );

        return Response::json($res, 201);
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show()
    {
        $input = Input::all();
        $user = Auth::user();
        if (array_key_exists('username', $input)) {
            $checkUser = User::where('username', '=', $input['username'])->firstOrFail(array('id', 'name', 'username'));
        } else if (array_key_exists('user_id', $input)) {
            $checkUser = User::where('id', '=', $input['user_id'])->firstOrFail(array('id', 'name', 'username'));
        } else {
            $res = array(
                'meta' => array(
                    'statuscode' => 400,
                    'message' => 'Correct identification for relationship to show was not provided'
                )
            );

            return Response::json($res, 400);
        }

        $res = array(
            'meta' => array(
                'statuscode' => 200,
            )
        );
        $res['follows_you'] = RelationshipHelpers::follows_you($checkUser);
        $res['you_follow'] = RelationshipHelpers::you_follow($checkUser);

        return Response::json($res, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {
        //
    }


}