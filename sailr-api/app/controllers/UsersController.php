<?php

class UsersController extends \BaseController
{

    /**
     * Display a listing of users
     *
     * @return Response
     */
    public function index()
    {
        $users = User::all();

        return View::make('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user
     *
     * @return Response
     */
    public function create()
    {
        return View::make('users.create');
    }

    /**
     * Store a newly created user in storage.
     *
     * @return Response
     */
    public function store()
    {
        $input = Input::all();
        $validator = Validator::make($input, User::$rules);
        if ($validator->fails()) {
            $res = array(
                'meta' => array(
                    'statuscode' => 400,
                    'message' => 'Invalid data',
                    'errors' => $validator->messages()->all()
                )
            );
            return Response::json($res, 400);
        }
        $input['password'] = Hash::make($input['password']);

        $input['name'] = e($input['name']);

        if(array_key_exists('bio', $input)) {
            $input['bio'] = e($input['bio']);
        }
        $user = User::create($input);

        $res = array(
            'meta' => array(
                'statuscode' => 201,
                'message' => 'Account successfully created'
            ),

            'data' => $user->toArray()
        );
        Auth::attempt(array('email' => $input['email'], 'password' => $input['password']), true, true);
        ProfileImg::setDefaultProfileImages($user);

        return Response::json($res, 201);
    }

    /**
     * Display the specified user.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        $user = User::where('id', '=', $id)->with('ProfileImg')->firstOrFail(array('id', 'name', 'username', 'bio'));
        if (!$user) {
            $res = array(
                'meta' => array(
                    'statuscode' => 404,
                    'message' => 'User not found'
                )
            );
            return Response::json($res, 404);
        }

        $following = Relationship::where('user_id', '=', $id)->count();
        $followers = Relationship::where('follows_user_id', '=', $id)->count();

        $userArray = $user->toArray();
        $userArray['follows_you'] = RelationshipHelpers::follows_you($user);
        $userArray['you_follow'] = RelationshipHelpers::you_follow($user);

        $items = Item::where('user_id', '=', $id)->with(array(
            'Photos' => function ($y) {
                    $y->select(['item_id', 'type', 'url']);
                },
        ))->orderBy('created_at', 'dsc')->get()->toArray();
        $res = array(
            'meta' => array(
                'responsecode' => 200,
                'url' => 'http://sailr.co/' . $user->username,
            ),
            'data' => array(
                'user' => $userArray,
                'counts' => array(
                    'following' => $following,
                    'followers' => $followers
                ),
                'items' => $items
            )
        );


        return Response::json($res);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        //User::destroy($id);

        return Redirect::route('users.index');
    }

    public function update()
    {
        $input = Input::all();
        $input = array_filter($input);
        $user = User::find(Auth::user()->id);

        $validator = Validator::make($input, User::$updateRules);
        if ($validator->fails()) {
            $res = array(
                'meta' => array(
                    'statuscode' => 400,
                    'message' => 'Invalid data',
                    'errors' => $validator->messages()->all()
                )
            );
            return Response::json($res, 400);
        }


        if (isset($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        }


        if(array_key_exists('bio', $input)) {
            $input['bio'] = e($input['bio']);
        }

        if(array_key_exists('name', $input)) {
            $input['name'] = e($input['name']);
        }

        $user->fill($input);
        $user->save();

        $res = array(
            'meta' => array(
                'statuscode' => 200,
                'message' => 'User successfully updated'
            ),

            'data' => $user->toArray()
        );
        return Response::json($res, 200);


    }

    public function self_feed()
    {
        $user = Auth::user();
        $following = Relationship::where('user_id', '=', $user->id)->get(array('follows_user_id'));
        $following = $following->toArray();

        $arrayOne = array();

        $counter = 0;
        foreach ($following as $key => $value) {
            $arrayOne[$counter] = $value['follows_user_id'];
            $counter = $counter + 1;
        }

        /*
         * The following line makes sure the the user's own posts show up in the feed!
         */

        $arrayOne[(count($arrayOne) + 1)] = Auth::user()->id;
        $items = Item::whereIn('user_id', $arrayOne)->with(array(

            'Photos' => function ($y) {
                    $y->select(['item_id', 'type', 'url']);
                },

            'User' => function ($q) {
                    $q->with('ProfileImg');
                    $q->select(['id', 'username', 'name']);

                }

        ))->orderBy('created_at', 'dsc')->get()->toArray();
        $res = array(
            'meta' => array(
                'statuscode' => 200
            ),

            'data' => $items
        );

        return Response::json($res);
    }
    
}
