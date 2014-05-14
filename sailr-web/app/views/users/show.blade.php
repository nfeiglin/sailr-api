@extends('layout.simple')
	@section('content')

{{-- <pre>{{ print_r($items) }}</pre>
<pre>{{ print_r($user) }}</pre>

--}}
		<div class="form-signin wide panel">
            <?php $user['profile_img'][1]['url'] = 'http://sailr.web/img/default-md.jpg' ?>
            <?php $user['profile_img'][0]['url'] = 'http://sailr.web/img/default-sm.jpg' ?>

            @foreach($user['profile_img'] as $prof_array)
             @if ($prof_array['type'] == 'medium')
            <img src="{{ $prof_array['url'] }}" alt="{{{ $user['name']}}}'s profile image" class="img-circle img-responsive center-block">
             @endif
            @endforeach

            <a href="{{ URL::action('UsersController@show', $user['username'] ) }}" class="h2"> {{{ $user['name'] }}}</a>
            <a href="{{ URL::action('UsersController@show', $user['username'] ) }}" class="h4">{{{ $user['username'] }}}</a>
            @if(Auth::check() && $is_self)
                <a href="#" class="btn btn-md btn-default pull-right">Settings</a>
            @endif
            <div class="row">
                <div class="col-sm-4 center-block">
                    <p class="h6">Following</p>
                    <p>{{ $no_of_following }}</p>
                </div>

                <div class="col-sm-4 center-block">
                    <p class="h6">Following</p>
                    <p>{{ $no_of_followers }}</p>
                </div>
            </div>
            @if($you_follow != true && Auth::check() && !$is_self)
            {{ Form::open(['action' => 'RelationshipsController@store' ]) }}
                <input type="hidden" value="{{{ $user['username'] }}}" name="username">
                <button class="btn btn-lg btn-primary btn-block" type="submit">Follow</button>
            {{ Form::close() }}

            @endif

            @if($you_follow == true && Auth::check() && !$is_self)
                {{ Form::open(['action' => 'RelationshipsController@destroy', 'method' => 'delete' ]) }}
                <input type="hidden" value="{{{ $user['username'] }}}" name="username">
                <button class="btn btn-lg btn-primary btn-block" type="submit" id="unfollow-btn">Following</button>
                @if($follows_you)
                    <p class="text-muted">{{{ $user['username'] }}} follows you</p>
                @endif
            {{ Form::close() }}
            @endif

            @if(!Auth::check())
                <a class="btn btn-lg btn-primary btn-block" href="#loginModal" data-toggle="modal" data-target="#loginModal" alt="Please log in to follow button">Follow {{{ $user['username'] }}}</a>
            @endif



            <p class="well">{{{ 'an incredible bio that is pretty cool and all goes here complete with lorem, ipsum and dolor'}}}</p>
            @if($user['bio']) <p class="well">{{{ $user['bio'] }}}</p> @endif
			@foreach($items as $item)

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
                        @if(Auth::check())
       						@if($user['id'] == Auth::user()->id)
							{{ Form::open(['action' => array('ItemsController@destroy', $item['id']), 'method' => 'delete']) }}
							{{Form::token()}}
								<button value="submit" class="glyphicon glyphicon-trash btn btn-danger btn-sm del-btn"></button>
							{{Form::close()}}
						@endif
                        @endif
      				</div>

    			</div>
        				<form action="{{ action('CommentsController@store') }}" method="post">
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
    	</div>

@if(!Auth::check())
<div class="modal fade" id="loginModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title">Please Login or Register to Follow</h3>
            </div>
            <div class="modal-body">
                @include('parts.not_logged_in')
            </div>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@endif
	@stop