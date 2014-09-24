<?php

use Sailr\Validators\CommentsValidator;
use Sailr\Api\Responses\Responder;

class CommentsController extends \BaseController
{
    /**
     * @var CommentsValidator
     * @var Responder
     */
    protected $commentsValidator;
    protected $responder;

    public function __construct(CommentsValidator $commentsValidator, Responder $responder) {
        $this->commentsValidator = $commentsValidator;
        $this->responder = $responder;
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $input = Input::all();

        $item = Item::where('id', '=', Input::get('item_id'))->firstOrFail(['id', 'public']);

        if ($item->public != 1) {
            return $this->responder->unauthorisedResponse("Can't comment on unpublished item");
        }

        $this->commentsValidator->validate($input, 'create');

        $comment = Comment::create([
                'user_id' => Auth::user()->id,
                'comment' => $input['comment'],
                'item_id' => $input['item_id']
            ]
        );

        Event::fire('comment.store', $comment);

        return $this->responder->createdModelResponse($comment);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        $comment = Comment::with('User')->where('id', $id)->firstOrFail();
        return $this->responder->showSingleModel($comment);
    }


    /**
     * Display comments for the specified item.
     *
     * @param int $item_id
     * @return Response
     */
    public function item_comments($item_id) {

        $comments = Comment::where('item_id', '=', $item_id)->orderBy('created_at', 'dsc')->with([
            'User' => function($u) {
              $u->select(['id', 'name', 'username']);
              $u->with(['ProfileImg' => function($p) {
                  $p->where('type', '=', 'medium');
                  $p->select(['url', 'user_id']);
              }]);
            },
        ])->get();

        return $this->responder->showSingleModel($comments);
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

        return $this->responder->noContentSuccess();
    }

}