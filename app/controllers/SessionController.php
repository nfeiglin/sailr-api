<?php

use Sailr\Api\Responses\Responder;

class SessionController extends BaseController
{

    /**
     * @var Responder
     */
    protected $responder;

    function __construct(Responder $responder)
    {
        $this->responder = $responder;
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {

        $input = Input::all();
        //$input['password'] = Hash::make($input['password']);
        //User::where('username', '=', 'mz');
        //$a = Auth::attempt($input, true, true);

        $authenticated = false;
        try {
            if ($input['username'][0] == '@') $input['username'] = ltrim($input['username'], '@');
            $user = User::where('username', '=', $input['username'])->orWhere('email', '=', $input['username'])->with(
                ['ProfileImg' => function($p) {
                    $p->where('type', 'medium');
                    $p->select(['url', 'user_id']);
            }])->firstOrFail();

            if (Hash::check($input['password'], $user->password)) {
                Auth::login($user, true);
                $authenticated = true;

                return $this->responder->showSingleModel($user);
            }

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

        }

        if (!$authenticated) {
            return $this->responder->errorMessageResponse("Username, email or password is incorrect");
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     *
     * @return Response
     */
    public function destroy()
    {
        Auth::logout();
        return $this->responder->noContentSuccess();
    }

}