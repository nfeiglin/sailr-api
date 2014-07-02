<?php

class SearchesController extends \BaseController {


	/**
	 * Display the specified resource.
	 * GET /s/{query}
	 *
	 * @param  string $initialQuery
	 * @return Response
	 */
	public function show($initialQuery)
	{
        $initialQuery = urldecode($initialQuery);

        $newQuery = $initialQuery;
        if($initialQuery[0] == '@') {
            $newQuery = ltrim($initialQuery, '@');
        }

        $userResults = User::whereLike('name', $newQuery)->orWhereLike('username', $newQuery)->with([
            'ProfileImg' => function($q) {
               $q->where('type', '=', 'small');
               //$q->first();
               $q->select(['url', 'user_id']);
            }
        ])->get(['id', 'name', 'username', 'bio'])->toArray();

        $itemResults = Item::where('public', '=', 1)->whereLike('title', $newQuery)->orWhereLike('description', $newQuery)->with([
            'Photos' => function($q) {
                $q->where('type', '=', 'thumbnail');
                //$q->first();
                $q->select(['url', 'item_id']);
            },

            'User' => function($u) {
                //$u->first();
                $u->select(['id', 'name', 'username']);
            }
        ])->get(['id', 'user_id', 'title', 'currency', 'price', 'public'])->toArray();

        $index = 0;
       foreach($itemResults as $item) {
           if($item['public'] == 0) {
               unset($itemResults[$index]);
           }

           $index++;
       }

        $res = json_encode(['users' => $userResults, 'items' => $itemResults]);

       return View::make('searches.show')
           ->with('results', $res)->
           with('title', 'Search / ' . htmlentities($initialQuery));


	}



}