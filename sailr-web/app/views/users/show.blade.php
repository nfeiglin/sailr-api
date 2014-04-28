@extends('layout.main')
	@section('content')
	<pre>{{ print_r($items) }}</pre>
	<pre>{{ print_r($user) }}</pre>

		<div class="col-lg-8 col-lg-offset-2 col-md-8 col-md-offset-2 col-sm-8 col-sm-offset-2 col-xs-12 well well-sm">
			
			@foreach($items as $item)
			<?php $user['profile_img'][0]['url'] = 'http://sailr.web/img/default-sm.jpg' ?>
			{{-- <pre> json_encode($item) </pre> --}}
			<div class="item" id="{{ $item['id'] }}">
				<div class="item-user panel panel-default">
						<a href="{{ action('UsersController@show', $user['username']) }}"><img src="{{ $user['profile_img'][0]['url'] }}" class="item-user-img img-circle"></a>
						<a href="{{ action('UsersController@show', $user['username']) }}" class="h4 name">{{ $user['name'] }} </a> <a href="{{ action('UsersController@show', $user['username']) }}"class="h5 username text-primary">{{ '@' . $user['username'] }}</a>
				</div>

				<div class="thumbnail">
					<div class="caption">
        				<h3> {{{ $item['title'] }}} </h3>
        				<p> {{{ $item['description'] }}}</p>
        			</div>

        			<div class="img-gallery">
						@foreach($item['photos'] as $photo)
      						<div class="gallery-item">
      							<img draggable="false" src={{$photo['url']}} class="img-responsive" alt="...">
      						</div>
      					@endforeach
      				</div>

      				<div class="caption">
        				<a href="{{ action('BuyController@create', $item['id']) }}" class="btn btn-primary btn-lg btn-block h3" role="button">Buy now for {{$item['currency']}}{{$item['price']}}</a>
       						@if($user['id'] == Auth::user()->id)
							{{ Form::open(['action' => array('ItemsController@destroy', $item['id']), 'method' => 'delete']) }}
							{{Form::token()}}
								<button value="submit" class="glyphicon glyphicon-trash btn btn-danger btn-sm del-btn"></button>
							{{Form::close()}}
						@endif
      				</div>

    			</div>
        				<form action="{{ action('CommentsController@store') }}" method="post" class="">
        					{{ Form::token() }}
        					<input type="hidden" name="item_id" value="{{ $item['id'] }}">
        					<input type="text" name="comment" placeholder="Write a comment..." class="panel panel-default form-control h6">
        				</form>

        				@foreach($item['comment'] as $comment)
        					<?php $comment['user']['profile_img'][0]['url'] = 'http://sailr.web/img/default-sm.jpg' ?>
	        				<div class="panel panel-default item-user comment">
	        					<a href="{{{ action('UsersController@show', $comment['user']['username']) }}}"><img src="{{ $comment['user']['profile_img'][0]['url'] }}" class="profile-img img-circle"></a>
								<a href="{{{ action('UsersController@show', $comment['user']['username']) }}}"class="h6 text-primary">{{ '@' . $comment['user']['username'] }}</a>
								<span class="h6">{{{ $comment['comment'] }}}</span>

								@if($comment['user_id'] == Auth::user()->id)
								{{ Form::open(['action' => array('CommentsController@destroy', $comment['id']), 'method' => 'delete']) }}
								<button value="submit" class="glyphicon glyphicon-trash btn btn-danger btn-sm del-btn"></button>
								{{Form::close()}}
								@endif
	        				</div>
        				@endforeach

    		</div>
			@endforeach
		{{ $paginator->links() }}
    	</div>

	@stop