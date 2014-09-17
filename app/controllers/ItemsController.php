<?php

use Laracasts\Commander\CommanderTrait;
use Sailr\Item\PostNewItemCommand;

class ItemsController extends BaseController
{
    use CommanderTrait;

    /**
     * @var $validator \Sailr\Validators\ItemsValidator
     */
    protected $validator;
    protected $commandBus;

    public function __construct(\Sailr\Validators\ItemsValidator $validator, \Laracasts\Commander\CommandBus $commandBus) {
        $this->validator = $validator;
        $this->commandBus = $commandBus;

    }
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $items = Item::WhereUser(Auth::user())->get(['title', 'price', 'currency', 'id', 'public']);
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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */

    public function store()
    {
        $command = new PostNewItemCommand(Input::get('title'), Input::get('currency'), Input::get('price'), Auth::user()->getAuthIdentifier());
        return $this->commandBus->execute($command);

    }

    public function edit($id)
    {
        $item = Item::where('user_id', '=', Auth::user()->id)->with([
            'Photos' => function ($x) {
                $x->where('type', '=', 'thumbnail');
                $x->select(['item_id', 'set_id', 'url']);
            }
        ])->withTrashed()->where('id', '=', $id)->firstOrFail();

        return View::make('items.edit')->with('title', "Edit product | $item->title")->with('item', $item)->with('jsonItem', $item->toJson());
    }

    public function update($id)
    {
        $input = Input::json()->all();
        if (isset($input['created_at'])) {
            unset($input['created_at']);
        }

        if (isset($input['public'])) {
            unset($input['public']);
        }

        if (isset($input['description'])) {
            $input['description'] = Holystone::sanitize($input['description']);
        }

        if (isset($input['title'])) {
            $input['title'] = htmlentities($input['title']);
        }

        $input  = array_filter($input);

        $v = $this->validator->validate($input, 'update');

        if($v->fails()) {
            $res = ['errors' => $v->errors()];
            return Response::json($res, 400);
        }

        $item = Item::withTrashed()->where('user_id', '=', Auth::user()->id)->where('id', '=', $id)->firstOrFail();

        if (Auth::user()->id != $item->user_id) {
            $res = ['errors' => ['Sorry, you can only edit your own products']];
            return Response::json($res, 403);
        }

        $item->fill($input);
        $item->save();

        return Response::json($item->toArray(), 200);
    }

    public function toggleVisibility($id)
    {
        $item = Item::where('id', '=', $id)->where('user_id', '=', Auth::user()->id)->firstOrFail();
        $errors = [];


        $message = '';
        $res = [];
        if ($item->public) {
            $item->public = 0;
            $item->save();
            $message = 'Successfully unpublished';

            $res = [
                'message' => $message,
                'public' => $item->public
            ];

        } else {
            $numberOfPhotos = Photo::where('item_id', '=', $id)->count();
            if (!$numberOfPhotos > 0) {
                $error = 'Please add at least one photo before publishing';
                $res = ['errors' => $error];
                return Response::json($res, 400);
            }

            $v = $this->validator->validate($item->toArray(), 'publish');

            if ($v->fails()) {
                $res = ['errors' => $v->errors()->first()];
                return Response::json($res, 400);
            }


            $item->public = 1;
            $item->save();

            $message = 'Excellent. Successfully published';

            $res = [
                'message' => $message,
                'public' => $item->public
            ];
        }
        return Response::json($res, 200);

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
            'User' => function ($x) {
                $x->with('ProfileImg');
                $x->select(['id', 'name', 'username']);
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


}
