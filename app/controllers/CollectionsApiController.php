<?php

class CollectionsApiController extends \BaseController
{

    /**
     * Display a listing of the resource.
     * GET /collections
     * @param string $username
     * @return Response
     */
    public function index($username)
    {
        try {

            $user = User::where('username', $username)->firstOrFail(['id', 'name', 'username']);

            $collections = $user->collection()->where('public', 1)->get(['title', 'id', 'user_id', 'created_at']);

            if (count($collections) < 1) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException('no results returned for collections');
            }

            $results = [
                'user' => $user->toArray(),
                'collections' => $collections->toArray()
            ];

            return Response::json($results, 200);
        }

        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json(['error' => 'No collections found']);
        }
    }

    public function doesItemExistInCollection($item_id, $collection_id)
    {
        $count = DB::table('collection_item')->where('collection_id', $collection_id)->where('item_id', $item_id)->count();
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function favourite()
    {

        $id = Input::get('item_id');

        $user = User::findOrFail(12); //Auth::user()
        $item = Item::findOrFail($id, ['id', 'user_id']);
        $collection = new stdClass;

        if ($user->collection()->where('title', 'Likes')->count() < 1) {
            //So we better make a favourite collection
            $collection = Collection::create([
                'title' => 'Likes',
                'user_id' => $user->id
            ]);
        } else {
            $collection = $user->collection()->where('title', 'Likes')->first();
        }

        $res = Response::json(['message' => 'Successfully liked'], 201);

        $exists = $this->doesItemExistInCollection($id, $collection->id);
        //$collection->items()->where('item_id', $id)->count();

        if ($exists) {
            echo 'already liked';
            $res = Response::json(['message' => 'You have already liked this product'], 400);
        } else {
            $collection = $collection->items()->save($item);
        }

        return $res;
    }


    /**
     * Store a newly created resource in storage.
     * POST /collections
     *
     * @return Response
     */
    public function store()
    {
        //Validate...
        $input = Input::all();
        $input['user_id'] = Auth::user()->id;

        $collection = Collection::create(Input::all());

        return Response::json($collection->toArray());
    }

    /**
     * Display the specified resource.
     * GET /collections/{id}
     *
     * @param string $username
     * @param  int $id
     * @return Response
     */
    public function show($username, $id)
    {
        return View::make('collections.show');
    }

    public function getCollection($id) {

        $response = [];
        $collection = Collection::findOrFail($id);
        $response['items'] = $collection->items->toArray();
        $response['followers'] = $collection->users()->count();
        $response['user'] = $collection->user()->firstOrFail(['id', 'name', 'username'])->toArray();

        return Response::json($response);
    }

    /**
     * Update the specified resource in storage.
     * PUT /collections/{id}
     *
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /collections/{id}
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

}