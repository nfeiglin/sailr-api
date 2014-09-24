<?php

use Sailr\Repository\UsersRepository;
use Sailr\Validators\UsersValidator;
use Sailr\Api\Responses\Responder;
use Sailr\Users\DTO\BasicRelationship;
class UsersController extends \BaseController
{
    /**
     * @var \Sailr\Repository\UsersRepository
     */
    protected $repository;
    /**
     * @var \Sailr\Validators\UsersValidator
     */
    protected $usersValidator;

    /**
     * @var Responder
     */
    protected $responder;

    public function __construct(Sailr\Repository\UsersRepository $repository, \Sailr\Validators\UsersValidator $usersValidator, Responder $responder) {
        $this->repository = $repository;
        $this->usersValidator = $usersValidator;
        $this->responder = $responder;

    }

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

        $this->usersValidator->validate($input, 'create');

        $input['password'] = Hash::make($input['password']);

        $input['name'] = e($input['name']);

        if (array_key_exists('bio', $input)) {
            $input['bio'] = e($input['bio']);
        }
        $user = $this->repository->create($input);

        $user_id = $user->getAuthIdentifier();

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

        return $this->responder->createdModelResponse($user);
    }

    /**
     * Display the specified user.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {

        $user = $this->repository->getFirstOrFailBy('id', $id, ['id', 'name', 'username', 'bio'], ['ProfileImg']);

        $follow_you = '';
        $you_follow = '';

        if (Auth::check()) {
            $follow_you = RelationshipHelpers::follows_you($user);
            $you_follow = RelationshipHelpers::you_follow($user);
        }
        $no_of_followers = RelationshipHelpers::count_follows_user($user);
        $no_of_following = RelationshipHelpers::count_user_following($user);

        $user['relationship'] = (new BasicRelationship($no_of_followers, $no_of_following, $follow_you, $you_follow))->toArray();

        return $this->responder->showSingleModel($user);

    }

    /**
     * Display the specified user's follower.
     *
     * @param  int $id
     * @return Response
     */
    public function followers($id)
    {

        $user = new User;
        $user->id = $id;

        //TODO: Paginate
        $followers = RelationshipHelpers::get_follows_user($user)->toArray();

        $follow_you = null;
        $you_follow = null;
        if (Auth::check()) {

            $follow_you = RelationshipHelpers::follows_you($user);
            $you_follow = RelationshipHelpers::you_follow($user);
        }

        $no_of_followers = RelationshipHelpers::count_follows_user($user);
        $no_of_following = RelationshipHelpers::count_user_following($user);


        $relationship = new BasicRelationship($no_of_followers, $no_of_following, $follow_you, $you_follow);

        return $this->responder->showSingleModel(new \Illuminate\Support\Collection(['relationship' => $relationship, 'followers' => $followers]));
    }


    /**
     * Display the people that the specified user follows.
     *
     * @param  int $id
     * @return Response
     */
    public function following($id)
    {
        $user = new User;
        $user->id = $id;

        //TODO: Paginate
        $following = RelationshipHelpers::get_user_following($user)->toArray();

        $follow_you = null;
        $you_follow = null;
        if (Auth::check()) {

            $follow_you = RelationshipHelpers::follows_you($user);
            $you_follow = RelationshipHelpers::you_follow($user);
        }

        $no_of_followers = RelationshipHelpers::count_follows_user($user);
        $no_of_following = RelationshipHelpers::count_user_following($user);


        $relationship = new BasicRelationship($no_of_followers, $no_of_following, $follow_you, $you_follow);

        return $this->responder->showSingleModel(new \Illuminate\Support\Collection(['relationship' => $relationship, 'following' => $following]));
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

    public function update()
    {
        $forgetKeys = [];
        $input = Input::all();

        $user = User::findOrFail(Auth::user()->id);

        if ($input['username'] == $user->username) {
            $forgetKeys[0] = 'username';
        }

        if ($input['email'] == $user->email) {
            $forgetKeys[1] = 'email';
        }

        $newInput = Input::except($forgetKeys);

        $this->usersValidator->validate($newInput, 'update');

        if (array_key_exists('bio', $input)) {
            $input['bio'] = e($input['bio']);

        }

        if (array_key_exists('name', $input)) {
            $input['name'] = e($input['name']);

        }

        $newInput = array_filter($newInput);
        $user->fill($newInput);
        $user->save();

        return $this->responder->showSingleModel($user);
    }





}
