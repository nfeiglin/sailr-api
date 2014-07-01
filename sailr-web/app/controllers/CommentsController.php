<?php

class CommentsController extends \BaseController
{

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $input = Input::all();
        $validator = Validator::make($input, Comment::$rules);

        if ($validator->fails()) {
            return Redirect::back()->with('message', 'Oh no! There is an issue with your comment.')->withErrors($validator);
        }


        $comment = Comment::create([
                'user_id' => Auth::user()->id,
                'comment' => $input['comment'],
                'item_id' => $input['item_id']
            ]
        );

        Event::fire('comment.store', $comment);

        return Redirect::back()->with('success', 'Woo! Comment posted successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        $comment = Comment::findOrFail($id)->with('User');

        $res = array(
            'meta' => array(
                'statuscode' => 200,
            ),
            'data' => $comment->toArray()
        );
        return Response::json($res, 200);
    }


    /**
     * Display comments for the specified item.
     *
     * @param string $username
     * @param int $id
     * @return Response
     */
    public function item_comments($username, $id) {
        $comments = Comment::where('item_id', '=', $id)->with([
            'User' => function($u) {
              $u->select(['id', 'name', 'username']);
              $u->with(['ProfileImg' => function($p) {
                  $p->where('type', '=', 'small');

              }]);
            },
        ])->get();

        $res = array(
            'meta' => array(
                'statuscode' => 200,
            ),
            'data' => $comments->toArray()
        );
        return Response::json($res, 200);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        $comment = Comment::where('id', '=', $id)->where('user_id', '=', Auth::user()->id)->firstOrFail();
        Event::fire('comment.destroy', $comment);
        $comment->delete();

        return Redirect::back()->with('success', 'Comment successfully deleted.');
    }

}