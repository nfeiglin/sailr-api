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
            $res = array(
                'meta' => array(
                    'statuscode' => 400,
                    'message' => 'Oh no! There is an issue with your comment',
                    'errors' => $validator->messages()->all()
                )
            );
            return Response::json($res, 400);
        }


        $comment = Comment::create([
                'user_id' => Auth::user()->id,
                'comment' => $input['comment'],
                'item_id' => $input['item_id']
            ]
        );

        Event::fire('comment.store', $comment);

        $res = array(
            'meta' => array(
                'statuscode' => 201,
                'message' => 'Comment successfully created'
            ),
            'data' => $comment->toArray()
        );
        return Response::json($res, 201);
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
     * @param int $id
     * @return Response
     */
    public function item_comments($id) {
        $comments = Comment::where('item_id', '=', $id)->with('User')->get();

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

        $res = array(
            'meta' => array(
                'statuscode' => 200,
                'message' => 'Comment successfully deleted'
            ),

        );
        return Response::json($res, 200);
    }

}