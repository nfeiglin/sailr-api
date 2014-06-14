<?php

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

        $card = $user->subscription()->getStripeCustomer()->cards->all(['limit' => 1])['data'][0];
        //dd($card);

        $cardType = $card->type;
        $last4 = $card->last4;
        $title = 'Billing Settings';

		return View::make('settings.billing', compact('cardType', 'last4', 'title', 'user'));
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
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
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
		$stripeToken = Input::json('stripeToken');
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