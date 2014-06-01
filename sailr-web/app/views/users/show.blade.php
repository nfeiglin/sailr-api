@extends('layout.profile.main')
@section('below')

{{--
<pre>{{ print_r($items) }}</pre>
<pre>{{ print_r($user) }}</pre>

--}}

@foreach($items as $item)
<div class="item" id="{{ $item['id'] }}">

    <div class="thumbnail">
        <div class="caption">
            <a href="{{ action('BuyController@create', $item['id']) }}"><h3> {{{ $item['title'] }}} </h3></a>

            <p> {{ $item['description'] }}</p>
        </div>

        <div class="img-gallery">
            @foreach($item['photos'] as $photo)
                <div class="gallery-item">
                    <img draggable="false" src="{{ $photo['url'] }}" class="img-responsive" alt="...">
                </div>
            @endforeach
        </div>

        <div class="caption">
            <a href="{{ action('BuyController@create', $item['id']) }}" class="btn btn-primary btn-lg btn-block h3"
               role="button">Buy now for {{$item['currency']}}{{$item['price']}}</a>
        </div>

    </div>
    <form action="{{ action('CommentsController@store') }}" method="post">
        {{ Form::token() }}
        <input type="hidden" name="item_id" value="{{ $item['id'] }}">
        <input type="text" name="comment" placeholder="Write a comment..." class="panel panel-default form-control h6">
    </form>

    @foreach($item['comment'] as $comment)
    <div class="panel panel-default item-user comment">
        <a href="{{{ action('UsersController@show', $comment['user']['username']) }}}"><img
                src="{{ $comment['user']['profile_img'][0]['url'] or ''}}" class="profile-img img-circle"></a>
        <a href="{{{ action('UsersController@show', $comment['user']['username']) }}}" class="h6 text-primary">{{ '@' .
            $comment['user']['username'] }}</a>
        <span class="h6">{{{ $comment['comment'] }}}</span>

        @if(Auth::check())
            @if($comment['user_id'] == Auth::user()->id)
                {{ Form::open(['action' => array('CommentsController@destroy', $comment['id']), 'method' => 'delete']) }}
                <button value="submit" class="glyphicon glyphicon-trash btn btn-danger btn-sm del-btn"></button>
            {{Form::close()}}
            @endif
        @endif
    </div>
    @endforeach

</div>
@endforeach
{{ $paginator->links() }}


@stop