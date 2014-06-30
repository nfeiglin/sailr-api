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
        $creditCardToken = Input::get('stripeToken');
        $subscriptionID = 'awesome';

        /* FOR TESTING ONLY AS IT CALLS THE STRIPE API EVERY TIME */
        $hasSubscription = Auth::user()->subscription()->getStripeCustomer()->subscription;
        //$hasSubscription = $user->subscribed();

        if ($hasSubscription) {
            $res = ['message' => 'You are already subscribed'];
            return Response::json($res, 400);
        }

        $subscription = $user->subscription($subscriptionID);

        if (Input::has('coupon')) {
            $subscription->withCoupon(Input::get('coupon'));
        }
        $subscription->create($creditCardToken,
            ['email'=> $user->email,
            'metadata' => ['name' => $user->name, 'user_id' => $user->id]
            ]
        );

        $res = ['message' => "✓ Subscription successful. You're officially awesome!", 'redirect_url' => URL::to('/')];
        return Response::json($res, 201);

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
	 * @return Response
	 */
	public function destroy()
	{
		$user = Auth::user();

		if($user->subscribed()) {
			$user->subscription()->cancel();
            $res = ['message' => 'Subscription canceled'];
            Event::fire('user.subscription.cancel');

			return Response::json($res, 200);
		}

		else {
            $res = ['message' => 'You do not have a subscription to cancel'];
			return Response::json($res, 400);
		}
	}

}