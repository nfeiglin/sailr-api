@extends('layout.main')
	@section('content')
	<pre>{{print_r($item)}} </pre>
		<div class="col-lg-8 col-lg-offset-2 col-md-8 col-md-offset-2 col-sm-8 col-sm-offset-2 col-xs-12 well well-sm">
			
			<?php $item['user']['profile_img'][0]['url'] = 'http://sailr.web/img/default-sm.jpg' ?>
			{{-- <pre> json_encode($item) </pre> --}}
			<div class="item" id="{{ $item['id'] }}">
				<div class="item-user panel panel-default">
						<a href="{{ action('UsersController@show', $item['user']['username']) }}"><img src="{{ $item['user']['profile_img'][0]['url'] }}" class="item-user-img img-circle"></a>
						<a href="{{ action('UsersController@show', $item['user']['username']) }}" class="h4 name">{{ $item['user']['name'] }} </a> <a href="{{ action('UsersController@show', $item['user']['username']) }}"class="h5 username">{{ '@' . $item['user']['username'] }}</a>
				</div>

				<div class="item">
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

      				<div class="panel panel-default">
      					<div class="panel-heading">
      						<h4 class="panel-title">Shipping</h4>
      					</div>

      					@foreach($item['shipping'] as $shipping)
      					<div class="col-md-6 col-lg-6">
      					<div class="panel-content">
      							<h5>{{ $shipping['type'] }}</h5>
      							<p>{{ $item['currency']}} {{$shipping['price']}}</p>
      							<p>{{ $shipping['desc'] }}</p>
      						</div>
      					</div>
      				</div>
      					@endforeach

    			</div>

    		</div>

    	</div>

	@stop