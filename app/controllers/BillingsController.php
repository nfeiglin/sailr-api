<?php
use Sailr\Emporium\Merchant\Helpers\Objects\Stripe\Subscription as SubscriptionDataObject;
class BillingsController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 * GET /billings
	 *
	 * @return Response
	 */
	public function index()
	{
        $user = Auth::user();

        $invoices = $user->invoices();

        $isSubscribed = $user->subscribed();

        if (!$isSubscribed) {
            return Redirect::to('/')->withSuccess('You must be subscribed to access billing settings');
        }

        $cu = $user->subscription()->getStripeCustomer();
        try {
            $card = $cu->cards->all(['limit' => 1])['data'][0];
        }

        catch (Exception $e) {

        }

        if (isset($card)) {
            $cardType = $card->brand;
            $last4 = $card->last4;
        }



        $subscription =  SubscriptionDataObject::make($cu->subscription)->build()->toJson();

        $title = 'Billing Settings';

		return View::make('settings.billing', compact('cardType', 'last4', 'title', 'invoices', 'renewDateString', 'subscription'));
	}

	/**
	 * Show the form for creating a new resource.
	 * GET /billings/create
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 * POST /billings
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}

	/**
	 * Display the specified resource.
	 * GET /billings/{id}
	 *
	 * @param  string  $id The invoice id string
	 * @return Response
	 */
	public function show($id)
	{
        //Render a page with a basic html receipt

        $u = Auth::user();
        return $u->findInvoiceOrFail($id)->render([
            'vendor' => 'Sailr',
            'product' => 'Subscription'
        ]);

	}

	/**
	 * Show the form for editing the specified resource.
	 * GET /billings/{id}/edit
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
	 * PUT /billings/
	 *
	 * @return Response
	 */
	public function update()
	{
		$stripeToken = Input::get('stripeToken');
        $user = Auth::user();
        $res = ['message' => 'Card updated successfully'];

        try {
            $user->subscription()->updateCard($stripeToken);
        }
        catch (Stripe_CardError $e) {
            $res['message'] = $e->getMessage();
        }


        return Response::json($res, 200);
	}

	/**
	 * Remove the specified resource from storage.
	 * DELETE /billings/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}