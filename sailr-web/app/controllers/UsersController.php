<?php

class UsersController extends \BaseController
{


    /**
     * Show the form for creating a new user
     *
     * @return Response
     */
    public function create()
    {
        return View::make('users.create')->with('title', 'Sign up');
    }

    /**
     * Store a newly created user in storage.
     *
     * @return Response
     */
    public function store()
    {
        $input = Input::all();
        Input::flash();

        $validator = Validator::make($input, User::$rules);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        }

        $input['password'] = Hash::make($input['password']);

        $input['name'] = e($input['name']);

        if (array_key_exists('bio', $input)) {
            $input['bio'] = e($input['bio']);
        }
        $user = User::create($input);

        //We don't want to see the just created user's following / followers!

        Auth::attempt(array('email' => $input['email'], 'password' => $input['password']), true, true);
        ProfileImg::setDefaultProfileImages($user);

        return Redirect::to('/')->with('message', 'Signed up! Welcome to Sailr');
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
        $user->setAppends(['counts']);
        $res = array(
            'meta' => array(
                'responsecode' => 200,
                'url' => 'http://sailr.co/' . $user->username,
            ),
            'data' => $user->toArray()
        );


        return Response::json($res);
    }

    /**
     * Display the user's recent listings
     * @param int $id
     * @return Response
     */
    public function items($id)
    {

        $items = Item::where('user_id', '=', $id)->with(array(
            'Photos' => function ($y) {
                    $y->select(['item_id', 'type', 'url']);
                },
        ))->orderBy('created_at', 'dsc')->get()->toArray();

        $res = array(
            'meta' => array(
                'responsecode' => 200,
            ),
            'data' => $items
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

    public function update($id)
    {
        if (!$id == Auth::user()->id) {
            $res = array(
                'meta' => array(
                    'statuscode' => 403,
                    'message' => 'Not authorised',
                    'errors' => ['Sorry, you can only update your own account.']
                )
            );
            return Response::json($res, 403);
        }
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


        if (array_key_exists('bio', $input)) {
            $input['bio'] = e($input['bio']);
        }

        if (array_key_exists('name', $input)) {
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

        $arrayOne[(count($arrayOne) + 1)] = $user->id;
        $items = Item::whereIn('user_id', $arrayOne)->with(array(

            'Photos' => function ($y) {
                    $y->select(['item_id', 'type', 'url']);
                    $y->where(['type', '=', 'full_res']);
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

        return View::make('users.feed')->with('items', $items)->with('title', 'Home');
    }

}
