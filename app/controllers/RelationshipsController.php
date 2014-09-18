<?php

class RelationshipsController extends \BaseController
{
    /**
     * @var \Sailr\Api\Responses\Responder
     */

    protected $responder;

    /**
     * @param \Sailr\Api\Responses\Responder $responder
     */

    public function __construct(\Sailr\Api\Responses\Responder $responder) {
        $this->responder = $responder;
    }

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
           return $this->responder->errorMessageResponse(Lang::get('relationship.insufficient'));

        }

        $doesExist = DB::table('relationships')->where('user_id', '=', $user->id)->where('follows_user_id', '=', $followUser->id)->count();

        if ($doesExist | $followUser->id == $user->id) {

            $message = str_replace('{username}', $user->username, Lang::get('relationship.already-follow'));
            return $this->responder->errorMessageResponse($message);
        }

        $relationship = new Relationship();
        $relationship->user_id = $user->id;
        $relationship->follows_user_id = $followUser->id;

        $relationship->save();
        Event::fire('relationship.create', $relationship);

        return $this->responder->noContentSuccess();
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
            return $this->responder->errorMessageResponse(Lang::get('relationship.insufficient'));
        }

        $doesExist = DB::table('relationships')->where('user_id', '=', $user->id)->where('follows_user_id', '=', $followUser->id)->count();

        if (!$doesExist) {
            return $this->responder->errorMessageResponse(Lang::get('relationship.cant-unfollow'));
        }

        $relationship = Relationship::where('user_id', '=', $user->id)->where('follows_user_id', '=', $followUser->id);
        $relationship->delete();
        return $this->responder->noContentSuccess();
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
            return $this->responder->errorMessageResponse(Lang::get('relationship.insufficient'));
        }

        $relationship = new Relationship();
        $relationship['follows_you'] = RelationshipHelpers::follows_you($checkUser);
        $relationship['you_follow'] = RelationshipHelpers::you_follow($checkUser);

        return $this->responder->showSingleModel($relationship);
    }



}