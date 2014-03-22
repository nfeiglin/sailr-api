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

        $user = User::create($input);
        $user = User::findOrFail($user->id);
        $res = array(
            'meta' => array(
                'statuscode' => 200,
                'message' => 'Account successfully created'
            ),

            'data' => $user
        );
        User::Authenticate(array('username' => $input['username'], 'password' => $input['password']));
        return Response::json($res);
    }

    /**
     * Display the specified user.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        $user = User::where('id', '=', $id)->first(array('id', 'name', 'email', 'username', 'bio'));
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
        $res['data']['counts'] = [
            'following' => $following,
            'followers' => $followers
        ];

        $res = array(
            'meta' => array(
                'responsecode' => 200,
                'url' => 'http://sailr.co/' . $user->username,
            ),
            'data' => array(
                $user->toArray(),
                'counts' => array(
                    'following' => $following,
                    'followers' => $followers
                )
            )
        );


        return Response::json($res);
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        $user = User::find($id);

        return View::make('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {


        return Redirect::route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        User::destroy($id);

        return Redirect::route('users.index');
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

        $items = Item::whereIn('user_id', $arrayOne)->with(array(

            'Photos' => function ($y) {
                    $y->select(['item_id', 'type', 'url']);
                },

            'User' => function ($q) {
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

    public function set_profile_image() {
        $user = User::findOrFail(User::getUserFromSession()->id);
        $user->ProfileImg->url = 'http://test.com';
        $user->ProfileImg->type = 'tpye';


    }
}