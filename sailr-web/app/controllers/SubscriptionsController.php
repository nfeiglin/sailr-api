<?php

class SubscriptionsController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 * GET settings/subscriptions
	 *
	 * @return Response
	 */
	public function index()
	{
		return View::make('settings.subscription')
            ->with('title', 'Settings / Subscription')
            ->with('user', Auth::user())
            ;
	}


	/**
	 * Store a newly created resource in storage.
	 * POST /subscriptions
	 *
	 * @return Response
	 */
	public function store()
	{
		$user = Auth::user();
        $input = Input::all();
        $creditCardToken = $input['stripeToken'];
        $subscriptionID = 'awesome';

        if ($user->subscribed()) {
            return Redirect::back()->with('message', 'You are already subscribed');
        }
        $user->subscription($subscriptionID)->create($creditCardToken);
        $customer = $user->subscription()->getStripeCustomer();

        $customer->email = $user->email;
        $customer->metadata = ['name' => $user->name, 'user_id' => $user->id];
        $customer->save();



        dd($input);

	}

	/**
	 * Display the specified resource.
	 * GET /subscriptions/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 * GET /subscriptions/{id}/edit
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 * PUT /subscriptions/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 * DELETE /subscriptions/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy()
	{
		$user = Auth::user();

		if($user->isSubscribed()) {
			$user->subscription->cancel();
			return Redirect::back()->with('message', 'Subscription canceled');
		}

		else {
			return Redirect::back()->with('fail', 'You do not have a subscription to cancel');
		}
	}

}