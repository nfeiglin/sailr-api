@extends('layout.main')
	@section('content')
	{{-- <pre>{{print_r($items)}}</pre> --}}
		<div class="col-lg-8 col-lg-offset-2 col-md-8 col-md-offset-2 col-sm-8 col-sm-offset-2 col-xs-12 well well-sm">
			
			@foreach($items as $item)
			{{-- <pre> json_encode($item) </pre> --}}
			<div class="item" id="{{ $item['id'] }}">
				<div class="item-user panel panel-default">
						<a href="{{ action('UsersController@show', $item['user']['username']) }}"><img src="{{ $item['user']['profile_img'][0]['url'] }}" class="item-user-img img-circle"></a>
						<a href="{{ action('UsersController@show', $item['user']['username']) }}" class="h4 name">{{ $item['user']['name'] }} </a> <a href="{{ action('UsersController@show', $item['user']['username']) }}"class="h5 username text-primary">{{ '@' . $item['user']['username'] }}</a>
				</div>

				<div class="thumbnail">
					<div class="caption">
        				<a href="{{ action('BuyController@create', $item['id']) }}"><h3>{{{ $item['title'] }}}</h3></a>
        			</div>

        			<div class="img-gallery">
						@foreach($item['photos'] as $photo)
      						<div class="gallery-item">
      							<img draggable="false" src="{{ $photo['url'] }}" class="img-responsive" alt="...">
      						</div>
      					@endforeach
      				</div>

      				<div class="caption">
        				<a href="{{ action('BuyController@create', $item['id']) }}" class="btn btn-primary btn-lg btn-block h3" role="button">Buy now for {{$item['currency']}}{{$item['price']}}</a>
      				</div>

    			</div>
        				<form action="{{ action('CommentsController@store') }}" method="post" class="">
        					{{ Form::token() }}
        					<input type="hidden" name="item_id" value="{{ $item['id'] }}">
        					<input type="text" name="comment" placeholder="Write a comment..." class="panel panel-default form-control h6">
        				</form>

        				@foreach($item['comment'] as $comment)
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