<?php

class OnboardController extends \BaseController {

    public function getRecentProducts($offset = null, $limit = null) {

        if (!isset($offset)) {
            $offset = 0;
        }

        if (!isset($limit)) {
            $limit = 25;
        }

        $products = Item::where('public', '=', 1)->skip((int)$offset)->take((int)$limit)->orderBy('created_at', 'dsc')->with([
            'User' => function($y)
            {
                $y->select(['id', 'name', 'username']);
            },

            'Photos' => function($p) {
                $p->where('type', '=', 'full_res');
                $p->select(['item_id', 'url']);
            }
        ])->get(['id', 'user_id', 'created_at', 'title', 'price', 'currency', 'ships_to']);

        return Response::json($products);
    }

}