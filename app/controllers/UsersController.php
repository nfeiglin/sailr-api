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
        $user_id = $user->id;
        Queue::push(function($job) use ($user_id){
            $user = User::find($user_id);
            ProfileImg::setDefaultProfileImages($user);

            $job->delete();

        });

        //ProfileImg::setDefaultProfileImages($user);

        //We don't want to see the just created user's following / followers!

        //Auth::attempt(array('email' => $input['email'], 'password' => $input['password']), true, true);
        Auth::login($user);


        Event::fire('user.create', $user);

        //Take them to the choose plan page!
        return Redirect::action('choose-plan')->with('message', 'Signed up! Welcome to Sailr');
    }

    /**
     * Display the specified user.
     *
     * @param  string $username
     * @return Response
     */
    public function show($username)
    {

        //How many results per page?
        $resultsPerPage = 30;

        $user = User::where('username', '=', $username)->with('ProfileImg')->firstOrFail(array('id', 'name', 'username', 'bio'));

        $items = Item::where('user_id', '=', $user->id)->where('public', '=', 1)->with(['User', 'Photos' => function($y) {
          $y->where('type', '=', 'full_res');
          $y->select(['url', 'id', 'item_id']);
        }]);


        $paginator = $items->paginate($resultsPerPage);
        $items = $items->get();
        $items = $items->toArray();




        $isSelf = false;
        $follow_you = false;
        $you_follow = false;
        if (Auth::check()) {
            if (Auth::user()->username == $username) {
                $isSelf = true;
            }

            $follow_you = RelationshipHelpers::follows_you($user);
            $you_follow = RelationshipHelpers::you_follow($user);
        }


        if (Auth::check()) {

        }

        $no_of_followers = RelationshipHelpers::count_follows_user($user);
        $no_of_following = RelationshipHelpers::count_user_following($user);

        $mutual = false;

        if ($follow_you && $you_follow) {
            $mutual = true;
        }

        $userArray = $user->toArray();

        return View::make('users.show')
        ->with('title', $user['username'])
        ->with('user', $userArray)
        ->with('items', $items)
        ->with('paginator', $paginator)
        ->with('follows_you', $follow_you)
        ->with('you_follow', $you_follow)
        ->with('mutual', $mutual)
        ->with('is_self', $isSelf)
        ->with('no_of_followers', $no_of_followers)
        ->with('no_of_following', $no_of_following)
        ;
    }

    /**
     * Display the specified user's follower.
     *
     * @param  string $username
     * @return Response
     */
    public function followers($username)
    {

        //How many results per page?
        $resultsPerPage = 30;

        $user = User::where('username', '=', $username)->with('ProfileImg')->firstOrFail(array('id', 'name', 'username', 'bio'));
        $followers = RelationshipHelpers::get_follows_user($user)->toArray();

        $userArray = $user->toArray();

        $isSelf = false;
        $follow_you = false;
        $you_follow = false;
        if (Auth::check()) {
            if (Auth::user()->username == $username) {
                $isSelf = true;
            }

            $follow_you = RelationshipHelpers::follows_you($user);
            $you_follow = RelationshipHelpers::you_follow($user);
        }


        $no_of_followers = RelationshipHelpers::count_follows_user($user);
        $no_of_following = RelationshipHelpers::count_user_following($user);

        $mutual = false;

        if ($follow_you && $you_follow) {
            $mutual = true;
        }
        return View::make('users.followers')
            ->with('title', $user['username'])
            ->with('user', $userArray)
            ->with('followers', $followers)
            ->with('follows_you', $follow_you)
            ->with('you_follow', $you_follow)
            ->with('mutual', $mutual)
            ->with('is_self', $isSelf)
            ->with('no_of_followers', $no_of_followers)
            ->with('no_of_following', $no_of_following)
            ->with('page_type', 'Followers')
            ;
    }


    /**
     * Display the people that the specified user follows.
     *
     * @param  string $username
     * @return Response
     */
    public function following($username)
    {

        //How many results per page?
        $resultsPerPage = 30;

        $user = User::where('username', '=', $username)->with('ProfileImg')->firstOrFail(array('id', 'name', 'username', 'bio'));
        $followers = RelationshipHelpers::get_user_following($user)->toArray();

        $userArray = $user->toArray();

        $isSelf = false;
        $follow_you = false;
        $you_follow = false;
        if (Auth::check()) {
            if (Auth::user()->username == $username) {
                $isSelf = true;
            }

            $follow_you = RelationshipHelpers::follows_you($user);
            $you_follow = RelationshipHelpers::you_follow($user);
        }


        $no_of_followers = RelationshipHelpers::count_follows_user($user);
        $no_of_following = RelationshipHelpers::count_user_following($user);

        $mutual = false;

        if ($follow_you && $you_follow) {
            $mutual = true;
        }
        return View::make('users.followers')
            ->with('title', $user['username'])
            ->with('user', $userArray)
            ->with('followers', $followers)
            ->with('follows_you', $follow_you)
            ->with('you_follow', $you_follow)
            ->with('mutual', $mutual)
            ->with('is_self', $isSelf)
            ->with('no_of_followers', $no_of_followers)
            ->with('no_of_following', $no_of_following)
            ->with('page_type', 'Following')
            ;
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

        //return Redirect::route('users.index');
    }

    public function update($id)
    {

    }

    /*
     * Display the user's homepage feed
     * @param null
     * @return Response
     */
    public function self_feed()
    {   
        //How many results per page?
        $resultsPerPage = 30;
        
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

        $items = Item::whereIn('user_id', $arrayOne)->where('public', '=', 1)->with(array(

            'Photos' => function ($y) {
                    $y->where('type', '=', 'full_res');
                    $y->select(['item_id', 'type', 'url']);
                    
                },

            'User' => function ($q) {
                    $q->with([
                            'ProfileImg' => function($z) {
                                $z->where('type', '=', 'small');
                                $z->first();
                            }
                        ]);
                    $q->select(['id', 'username', 'name']);

                },

            'Comment' => function($comment) {
                $comment->select(['id', 'item_id', 'user_id', 'comment', 'created_at']);
                $comment->orderBy('created_at', 'dsc');

                $comment->with([
                    'User' => function($user) {
                       $user->select(['id', 'name', 'username']);
                        $user->with([
                            'ProfileImg' => function($img) {
                                $img->where('type', '=', 'small');
                            }
                        ]);  
                }

                ]);
}

        ))->orderBy('created_at', 'dsc');

        $nextPageNumber = 2;
        if(isset($_GET['page'])) {
            $nextPageNumber = Input::get('page') + 1;
        }
        
        $paginator = $items->paginate($resultsPerPage);

        $next_url = $paginator->getUrl($nextPageNumber);
        $current_page = $paginator->getCurrentPage();
        $max_page = $paginator->getLastPage();

        $items = $items->get()->toArray();

        //echo $next_url;
/*
    $items['current_page'] = $current_page;
    $items['max_page'] = $max_page;
    $items['next_url'] = $next_url;
*/

        return View::make('users.feed')
            ->with('items', $items)
            ->with('title', 'Home')
            ->with('paginator', $paginator);
    }

}
