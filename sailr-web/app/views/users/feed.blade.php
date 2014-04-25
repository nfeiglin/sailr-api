@extends('layout.main')
	@section('content')
	{{-- <pre>{{print_r($items)}}</pre> --}}
		<div class="col-lg-8 col-lg-offset-2 col-md-8 col-md-offset-2 col-sm-8 col-sm-offset-2 col-xs-12 well well-sm">
			
			@foreach($items as $item)
			<?php $item['user']['profile_img'][0]['url'] = 'http://sailr.web/img/default-sm.jpg' ?>
			{{-- <pre> {{ json_encode($item) }}</pre> --}}
			<div class="item">
				<div class="item-user panel panel-default">
						<a href="{{ action('UsersController@show', $item['user']['username']) }}"><img src="{{ $item['user']['profile_img'][0]['url'] }}" class="item-user-img"></a>
						<a href="{{ action('UsersController@show', $item['user']['username']) }}" class="h4 name">{{ $item['user']['name'] }} </a> <a href="{{ action('UsersController@show', $item['user']['username']) }}"class="h5 username">{{ '@' . $item['user']['username'] }}</a>
				</div>

				<div class="thumbnail">
					<div class="caption">
        				<h3> {{ $item['title'] }} </h3>
        				<p> {{ $item['description'] }}</p>
        			</div>
					@foreach($item['photos'] as $photo)
      					<img src={{$photo['url']}} class="img-responsive" alt="...">
      				@endforeach
      				<div class="caption">
        				
        				<a href="{{ action('BuyController@create', $item['id']) }}" class="btn btn-primary btn-lg btn-block h3" role="button">Buy now for {{$item['currency']}}{{$item['price']}}</a>
        				
        				{{-- ALL THE OTHER COMMENTS GO HERE!! --}}
        				<form action="#" method="post" class="">
        				<input type="text" name="comment" placeholder="Write a comment..." class="form-control panel panel-default post-comment">
        				<input type="hidden" name="item_id" value="{{ $item['id'] }}"
        				</form>

      				</div>
    			</div>
    		</div>
			@endforeach
		{{ $paginator->links() }}
    	</div>
	@stop