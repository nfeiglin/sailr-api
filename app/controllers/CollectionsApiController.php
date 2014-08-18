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

    public function updateCollectionPreviewImage(Collection $collection, Item $item) {
        $url = $item->photos()->where('type', 'full_res')->orderBy('created_at', 'dsc')->first(['url']);

        if (!isset($url)) {
            $url = ['url' => 'https://sailr.co/img/default-lg.jpg'];
        }

        $collection->preview_image = $url['url'];
        $collection->save();

        return $collection;

    }

    public function favourite()
    {

        $id = Input::get('item_id');

        $user = User::findOrFail(12); //Auth::user()
        $item = Item::findOrFail($id, ['id', 'user_id', 'public']);

        $collection = [];

        if ($user->collection()->where('title', 'Likes')->count() < 1) {
            //So we better make a favourite collection
            $collection = new Collection;
            $collection->title = 'Likes';
            $collection->user_id = $user->id;
            $collection->save();

        } else {
            $collection = $user->collection()->where('title', 'Likes')->first();
        }

        $res = Response::json(['message' => 'Successfully liked'], 201);


        if ($this->doesItemExistInCollection($item->id, $collection->id)) {
            $res = Response::json(['message' => 'You have already liked this product'], 400);
        } else {
            if ($item->public != 1) return Response::json(['message' => 'Products that are not public can not be put into collections'], 403);

            $collection->items()->save($item);
            $this->updateCollectionPreviewImage($collection, $item);
        }

        return $res;
    }


    /**
     * Create a new collection and set its first product, or add a new product to a collection.
     * POST /collections
     *
     * @return Response
     */
    public function store()
    {

        $user = User::findOrFail(12); //Auth::user()
        $item = Item::findOrFail(Input::get('item_id'), ['id', 'user_id', 'public']);

        $input = Input::all();
        $validator = \Sailr\Validators\CollectionsValidator::make();

        $result = $validator->validate($input, 'update');
        if (!$result) {
            return Response::json(['message' => $validator->getErrorMessages()->first()], 400);
        }

        if ($item->public != 1) return Response::json(['message' => 'Products that are not public can not be put into collections'], 403);

        $createNewCollection = Input::has('collection_title');

        if ($createNewCollection) {

            $result = $validator->validate($input, 'create');
            if (!$result) {
                return Response::json(['message' => $validator->getErrorMessages()->first()], 400);
            }

            $collection = new Collection;
            $collection->title = $input['collection_title'];
            $collection->user_id = $user->id;
            $collection->save();
        }

        else {
            $result = $validator->validate($input, 'update');
            if (!$result) {
                return Response::json(['message' => $validator->getErrorMessages()->first()], 400);
            }

            try {
                $collection = Collection::findOrFail(Input::get('collection_id'));
            }

            catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return Response::json(['message' => "Sorry, we couldn't find a collection to add the product to"], 404);
            }
        }

        if ($this->doesItemExistInCollection($item->id, $collection->id)) {
            $res = Response::json(['message' => 'You already have this product in your collection'], 400);
        } else {

            $collection->items()->save($item);
            $this->updateCollectionPreviewImage($collection, $item);

            $res = Response::json($collection->toArray(), 201);
        }

        return $res;
    }

    public function getCollection($id, $currentCollection = null) {

        $response = [];

        if (isset($currentCollection)) {
            $collection = $currentCollection;
        }
        else {
            $collection = Collection::findOrFail($id);
        }

        $response['items'] = $collection->items()->get()->toArray();
        $response['followers'] = $collection->users()->count();
        $response['user'] = $collection->user()->firstOrFail(['id', 'name', 'username'])->toArray();

        return Response::json($response);
    }

    public function getLikes($user_id) {
        $c = Collection::where('title', 'Likes')->where('user_id', $user_id)->firstOrFail();
        return $this->getCollection($c, $c->id);
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
    public function destroyCollection($id)
    {
        /* TODO: Test this... it is faulty */

        $user = User::findOrFail(12); //Auth::user();

        $collection = Collection::findOrFail($id);

        if (!$this->canAdminCollection($collection, $user)) {
            return Response::json([], 403);
        }

        $collection->delete();
        return Response::json([], 204);


    }

    /**
     * Remove the specified resource from storage.
     * DELETE /collections/{id}
     *
     * @param  int $collection_id
     * @param int $item_id
     * @return Response
     */
    public function destroyCollectionItem($collection_id, $item_id)
    {
        /* TODO: Test this... it is faulty */

        $user = User::findOrFail(12); //Auth::user();

        $collection = Collection::findOrFail($collection_id);

        if (!$this->canAdminCollection($collection, $user)) {
            return Response::json(['message' => 'You do not have admin privileges on this collection'], 403);
        }

        $c = $collection->items()->where('id', $item_id)->get();//->detach();

        return Response::json($c->toArray(), 204);


    }

    public function canAdminCollection(Collection $collection, User $user) {

        if (!isset($collection->user_id)) {
            throw new Exception('The collection object is missing the user_id column');
        }
        if ($collection->user_id == $user->getAuthIdentifier()) {
            return true;
        }

        else if($collection->users()->where('role', 'admin')->where('user_id', $user->getAuthIdentifier())->count() >= 1) {
            return true;
        }

        return false;

    }


}