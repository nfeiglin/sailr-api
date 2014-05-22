<?php

class ItemsController extends BaseController
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $items = Item::WhereUser(Auth::user())->get(['title', 'price', 'currency']);
        return View::make('items.index')
            ->with('title', 'My products')
            ->with('items', $items->toArray())
            ->with('hasNavbar', 1);

    }




    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        return View::make('items.create')->with('title', 'Add a product');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */

    public function store() {

        $input = Input::all();
        $rules = [
            'title' => 'required|max:255',
            'currency' => 'required|currency|max:2',
            'price' => 'required|numeric|max:999999'
        ];

        //TODO add currency validator

        $v = Validator::make($input, $rules);

        if ($v->fails()) {

            //Kill it!
            return Response::json($v->messages(), 400);
        }

        $item = new Item();
        $item->title = $input['title'];
        $item->currency = $input['currency'];
        $item->price = $input['price'];
        $item->user_id = Auth::user()->id;

        $item->save();

        //TODO: Assume things are sweet and now move them onto the edit page to finish up.
        $res = [
            'message' => 'Success',
            'id' => $item->id,
            'redirect_url' => URL::action('ItemsController@edit')
          ];

        return Response::json($res, 201);

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        /*
         * Find the item listing from the DB
         */
        $item = Item::with(array(
            'Photos' => function ($y) {
                    $y->select(['item_id', 'type', 'url']);
                },
            'User' => function($x) {
                    $x->with('ProfileImg');
                  $x->select(['id', 'name', 'username']);
                },
            'Shipping' => function($z) {
                    $z->select(['type', 'price', 'desc', 'item_id']);
                },
        ))->where('id', '=', $id)->firstOrFail();

        /*
         * Generate and output the JSON response
         */
        $res = array(
            'meta' => array(
                'statuscode' => 200
            ),

            'data' => $item->toArray()
        );

        return Response::json($res);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        $item = Item::where('id', '=', $id)->where('user_id', '=', Auth::user()->id)->firstOrFail();
        if ($item->user_id != Auth::user()->id) {
            return Redirect::back()->with('message', 'Not authorised to delete that post');
        }

        $photos = Photo::where('item_id', '=', $item->id);
        $photos->delete();
        $item->delete();

        /*
         * TODO: When calling these from buyer's panel, use "whereTrashed"
         */

        return Redirect::back()->with('success', $item->title . ' deleted successfully');

    }

    public function doItemCreationFromInput(array $input, $user_id)
    {

        $item = new Item();
        $item->user_id = $user_id;
        $item->title = $input['title'];
        $item->description = $input['description'];
        $item->price = $input['price'];
        $item->currency = $input['currency'];
        $item->initial_units = $input['initial_units'];
        $item->country = $input['country'];
        $item->save();
        return $item;
    }

    public function doShippingFromInput(array $input, $item_id)
    {
        foreach(Shipping::$shippingTypes as $name => $values) {
            $priceKey = $values[0];
            $descKey = $values[1];

            $shipping = new Shipping();
            $shipping->item_id = $item_id;
            $shipping->type = $name;
            $shipping->price = $input[$priceKey];
            $shipping->desc = e($input[$descKey]);
            $shipping->save();
        }
    }

}
