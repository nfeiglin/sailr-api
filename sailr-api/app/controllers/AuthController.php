<?php

class AuthController extends BaseController
{

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

        try {
            if ($input['username'][0] == '@') $input['username'] = ltrim($input['username'], '@');
            $user = User::where('username', '=', $input['username'])->orWhere('email', '=', $input['username'])->firstOrFail();
            if(Hash::check($input['password'], $user->password)) {
                $a = Auth::loginUsingId($user->id);
            }

        }

        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Return User::loginFailResponse();
        }

        if (!$a) {
            return User::loginFailResponse();
        }

        $res = array(
            'meta' => array(
                'statuscode' => 200,
                'message' => 'Logged in successfully'
            ),

            'data' => $user->toArray()
        );
        return Response::json($res);
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
        $res = array(
            'meta' => array(
                'statuscode' => 200,
                'message' => 'Logged out'
            )
        );

        return Response::json($res);
    }

}