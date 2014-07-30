<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Comment extends \Eloquent {
    use SoftDeletingTrait;

    public static function boot() {
        parent::boot();

        Comment::creating(function(Comment $commentModel){
            //Email the seller of the product
            $product = $commentModel->item();
            $seller = $commentModel->user();

            //Dont email the seller if it was their comment, or the currently logged in user's comment
            if ($commentModel->user_id == $seller->id | $commentModel->user_id = Auth::user()->id) {

                $tagger = \Sailr\Tags\SailrTagger::make();
                $taggedUsers = $tagger->getTaggedUsers($commentModel->comment);

                foreach($taggedUsers as $taggedUser) {
                    //Email and notify them
                }
                return;
            }


                //Email people and add to their notifications
                Notification::create([
                'short_text' => 'New comment on ' . $product->title,
                'type' => 'user.follow',
                'user_id' => $product->,
                'data' => ['relationship' => $relationship->toArray()],
            ]);

        });
    }

	protected $fillable = ['user_id', 'item_id', 'comment'];
    protected $hidden = ['updated_at', 'deleted_at'];
    protected $softDelete = true;
    //protected $appends = ['user'];

    public static $rules = [
        'comment' => ['required', 'max:400'],
        'item_id' => ['required', 'exists:items,id']
    ];
    public function user() {
        return $this->belongsTo('User');
    }

    public function item() {
        return $this->belongsTo('Item');
    }


    public function getUserAttribute() {
        return User::find($this->user_id)->toArray();
    }


}

