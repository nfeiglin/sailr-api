<?php
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use Sailr\Observers\CollectionObserver;
class Collection extends \Eloquent {
    use SoftDeletingTrait;

    public static function boot() {
        parent::boot();
        Collection::observe(new CollectionObserver());
    }

    public $timestamps = true;
	protected $fillable = ['title', 'public', 'user_id'];
    protected $hidden = ['deleted_at'];

    public function items() {
        return $this->belongsToMany('Item')->where('public', '=', '1')->withTimestamps();//->orderBy('created_at', 'dsc');
    }

    public function users() {
        return $this->belongsToMany('User')->withTimestamps();
    }

    public function user() {
        return $this->belongsTo('User');
    }
}