<?php

class SettingsController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 * GET /settings
	 *
	 * @return Response
	 */
	public function index()
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 * GET /settings/{id}/edit
	 *
	 * @return Response
	 */
	public function getAccount()
	{
        return View::make('settings.account')->with('user', Auth::user())->with('title', 'Account Settings');
	}

	/**
	 * Update the specified resource in storage.
	 * PUT /settings/
	 *
	 * @return Response
	 */
	public function putAccount()
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

        $validator = Validator::make($newInput, User::$updateRules);
        if ($validator->fails()) {
           return Redirect::back()->with('fail', 'Invalid data')->withErrors($validator->errors())->withInput(Input::except(['username', 'email']));
        }


        if (array_key_exists('bio', $input)) {
            $input['bio'] = e($input['bio']);

        }

        if (array_key_exists('name', $input)) {
            $input['name'] = e($input['name']);

        }

        $newInput = array_filter($newInput);
        $user->fill($newInput);
        $user->save();

        return Redirect::back()->with('success', 'Bam. Updated successfully.');
	}

}