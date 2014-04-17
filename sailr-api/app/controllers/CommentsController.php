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

        $date = \Carbon\Carbon::now()->subMinutes(90);
        if (DB::table('comments')->where('comment', '=', $input['comment'])->where('user_id', '=', Auth::user()->id)->where('created_at', '<', $date)->count() >= 1) {
            $res = array(
                'meta' => array(
                    'statuscode' => 400,
                    'message' => 'Comment not posted... hmm...',
                    'errors' => ['We sense that you have posted the same comment recently']
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

        Event::fire('comment.store', $comment->with('User'));

        $res = array(
            'meta' => array(
                'statuscode' => 201,
                'message' => 'Comment successfully created'
            ),
            'data' => $comment->with('User')->toArray()
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