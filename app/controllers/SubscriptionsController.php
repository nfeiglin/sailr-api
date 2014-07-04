<?php
use Sailr\Emporium\Merchant\Helpers\Objects\Stripe\Subscription as SubscriptionDataObject;
class SubscriptionsController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 * GET settings/subscriptions
	 *
	 * @return Response
	 */
	public function index()
	{
        $user = Auth::user();
        $subscriptionObject = '{}';

        if ($user->subscribed()) {
            $stripeSub = $user->subscription()->getStripeCustomer()->subscription;
            $subscriptionObject =  SubscriptionDataObject::make($stripeSub)->build()->toJson();
        }

        return View::make('settings.subscription')
            ->with('title', 'Settings / Subscription')
            ->with('user', $user->toJson())
            ->with('subscription', $subscriptionObject)
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



        if ($user->subscribed()) {
            $res = ['message' => 'You are already subscribed'];
            return Response::json($res, 400);
        }

        $subscription = $user->subscription($subscriptionID);

        if (Input::has('coupon')) {
            $subscription->withCoupon(Input::get('coupon'));
        }


        try {

            $subscription->create($creditCardToken,
                ['email' => $user->email,
                    'metadata' => ['name' => $user->name, 'username' => $user->username, 'user_id' => $user->id]
                ]);

            $res = ['message' => "✓ Subscription successful. You're officially awesome!", 'redirect_url' => URL::to('/')];
            return Response::json($res, 201);

        } catch (Stripe_CardError $e) {
            // Since it's a decline, Stripe_CardError will be caught
            $body = $e->getJsonBody();
            $err = $body['error'];

            $res = ['message' => $err['message']];
            return Response::json($res, 400);

        } catch (Stripe_InvalidRequestError $e) {
            // Invalid parameters were supplied to Stripe's API
            $body = $e->getJsonBody();
            $err = $body['error'];

            $res = ['message' => $err['message']];
            return Response::json($res, 400);

        } catch (Stripe_AuthenticationError $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)

            $body = $e->getJsonBody();
            $err = $body['error'];

            $res = ['message' => $err['message']];
            return Response::json($res, 400);

        } catch (Stripe_ApiConnectionError $e) {
            // Network communication with Stripe failed
            $body = $e->getJsonBody();
            $err = $body['error'];

            $res = ['message' => $err['message']];
            return Response::json($res, 400);
        } catch (Stripe_Error $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            Log::error('Stripe error:: ' . print_r($e->getJsonBody(), 1));

            $body = $e->getJsonBody();
            $err = $body['error'];
            $res = ['message' => $err['message']];
            return Response::json($res, 400);
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            Log::error($e->getMessage() . $e->getTraceAsString());
            $res = ['message' => 'Sorry, an error has occurred'];
            return Response::json($res, 400);
        }


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