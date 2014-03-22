<?php

class SessionsController extends \BaseController
{

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $input = Input::all();
        if (Auth::attempt($input, true)) {
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
            )
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