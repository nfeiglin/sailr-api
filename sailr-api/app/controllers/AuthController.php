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
            $user = User::where('username', '=', $input['username'])->orWhere('email', '=', $input['username'])->where('password', '=', Hash::make($input['password']))->firstOrFail(array('id', 'username', 'name'))->toArray();
            $a = Auth::loginUsingId($user['id']);
        }

        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $res = array(
                'meta' => array(
                    'statuscode' => 401,
                    'message' => 'Username, email, or password is incorrect'
                )
            );
            return Response::json($res, 401);
        }

        if (!$a) {
            $res = array(
                'meta' => array(
                    'statuscode' => 401,
                    'message' => 'Username, email, or password is incorrect'
                )
            );
            return Response::json($res, 401);
        }

        $res = array(
            'meta' => array(
                'statuscode' => 200,
                'message' => 'Logged in successfully'
            ),

            'data' => $user
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