@extends('layout.main')
	@section('content')
	<script> 
	var item = {{ json_encode($item) }};
	var internationalShippingPrice = {{ $item['shipping'][1]['price'] }};
	var domesticShippingPrice = {{ $item['shipping'][0]['price'] }};
	</script>
		<div class="col-lg-6 col-md-6 col-sm-6 col-sm-7 col-xs-12">
			<h2>Item Info</h2>
				<div class="row">

      				<h4>Price</h4>
      				<p>{{ $item['currency']}}{{$item['price']}}</p>
      				
      				<h4>Shipping</h4>
      					@foreach($item['shipping'] as $shipping)
      						<div class="col-md-6 col-lg-6">
      								<h5 class="text-primary">{{ $shipping['type'] }}</h5>
      								<p class="h5">{{ $item['currency']}}{{$shipping['price']}}</p>
      								<p class="h5">{{{ $shipping['desc'] }}}</p>
      						</div>
      					@endforeach
</div>

			<?php $item['user']['profile_img'][0]['url'] = 'http://sailr.web/img/default-sm.jpg' ?>
			{{-- <pre> json_encode($item) </pre> --}}
			<div class="well item" id="{{ $item['id'] }}">
				<div class="item-user panel panel-default">
						<a href="{{ action('UsersController@show', $item['user']['username']) }}"><img src="{{ $item['user']['profile_img'][0]['url'] }}" class="item-user-img img-circle"></a>
						<a href="{{ action('UsersController@show', $item['user']['username']) }}" class="h4 name">{{ $item['user']['name'] }} </a> <a href="{{ action('UsersController@show', $item['user']['username']) }}"class="h5 username">{{ '@' . $item['user']['username'] }}</a>
				</div>

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
      				
    		</div>

 	</div>

    	<h2>Your Info</h2>
    <div class="col-lg-6 col-md-6 col-sm-6 col-sm-5 col-xs-12 jumbotron swag" style="background: url('{{ URL::asset('images/swag-bg.jpg') }}' no-repeat;">
    	{{ Form::open(['action' => ['BuyController@store', $item['id']], 'class' => 'form-horizontal', 'autocomplete' => 'off', 'validate', 'validate']) }}
    	{{ Form::token() }}
    	<h3>The Basics</h3>
    	<div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
    		<p class="form-control-static">{{{ Auth::user()->name }}}</p>
    		<p class="form-control-static">{{{ Auth::user()->email }}}</p>
    	</div>

    	<h3>The Important Stuff</h3>

    	<div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
    		<input type="text" placeholder="Start entering your address here..." class="form-control" id="autocomplete" onFocus="geolocate()" autocomplete="false" autocomplete="off">
    	</div>

  <div id="hidden-form">
    		<div class="col-md-6 col-lg-6 col-sm-6 col-xs-6 form-group">
    			<input type="text" class="form-control" placeholder="Number" name="street_number" class="form-inline" id="street_number" required="required">
    		</div>

    		<div class="col-md-6 col-lg-6 col-sm-6 col-xs-6 form-group">
    			<input type="text" placeholder="Street" name="street_name" class="form-control" id="route" required="required">
    		</div>

    	<div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
    		<input type="text" placeholder="City" name="city" class="form-control" id="locality" required="required">
    	</div>

       	<div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
    		<input type="text" placeholder="State" name="state" class="form-control" id="administrative_area_level_1" required="required">
    	</div>

    	<div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
    		<input type="text" placeholder="Zip code" name="zipcode" class="form-control" id="postal_code" required="required">
    	</div>

    	<div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
    		<input type="text" placeholder="Country" name="country" class="form-control" id="country" required="required">
    	</div>
</div>
	<p class="form-control-static col-md-10 col-sm-8 col-lg-10">Total price (including shipping):</p><p class="form-control-static col-md-2 col-sm-4 col-lg-12" id="total-price"></p>
	<button value="submit" class="btn btn-lg btn-block paypal-btn">Checkout with Paypal</button>
    	{{ Form::close() }}
    </div>

<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places"></script>
	@stop