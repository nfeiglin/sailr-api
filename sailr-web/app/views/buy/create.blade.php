@extends('layout.simple')

	@section('content')
	<script async="async" defer="defer">
	var item = {{ json_encode($item) }};
	var internationalShippingPrice = {{ $item['shipping'][1]['price'] or '999'}};
	var domesticShippingPrice = {{ $item['shipping'][0]['price']  or '999' }};
	</script>
    
		<div class="form-signin wide panel">
			<h2>{{{ $item['title'] }}} <small class="text-danger">{{ $item['currency']}}{{$item['price'] }}</small></h2>
            <button class="btn btn-primary btn-lg btn-block" data-toggle="modal" data-target="#myModal">Buy now {{ $item['currency']}}{{$item['price']}}</button>
				<div class="row">

</div>

			<?php $item['user']['profile_img'][0]['url'] = 'http://sailr.web/img/default-sm.jpg' ?>

			<div class="well item buy-page" id="{{ $item['id'] }}">
					<div class="caption">
        				<p> {{ $item['description'] }}</p>
        			</div>

        			<div class="img-gallery">
                        <?php $item['photos'][0]['url'] = 'http://sailr.web/img/default-lg.jpg' ?>
                        <?php $item['photos'][1]['url'] = 'http://sailr.web/img/default-lg.jpg' ?>
						@foreach($item['photos'] as $photo)
      						<div class="gallery-item">
      							<img draggable="false" src=" {{ $photo['url'] }}" class="img-responsive" alt="...">
      						</div>
      					@endforeach
      				</div>
                <div class="item-user panel panel-default" id="buy-user">
                    <a href="{{ action('UsersController@show', $item['user']['username']) }}"><img src="{{ $item['user']['profile_img'][0]['url'] }}" class="item-user-img img-circle"></a>
                    <a href="{{ action('UsersController@show', $item['user']['username']) }}" class="h4 name">{{ $item['user']['name'] }} </a> <a href="{{ action('UsersController@show', $item['user']['username']) }}"class="h5 username">{{ '@' . $item['user']['username'] }}</a>
                </div>

                <h4>Shipping</h4>
                <?php $shippings = $item['shipping'] ?>
                <div class="table">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <td>{{ $shippings[0]['type'] or ''}}</td>
                            <td>{{ $shippings[1]['type'] or '' }}</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>{{{ $shippings[0]['desc'] or '' }}}</td>
                            <td>{{{ $shippings[1]['desc'] or '' }}}</td>
                        </tr>
                            <tr>
                                <td>{{ $item['currency']}}{{$shippings[0]['price'] or ''}}</td>
                                <td>{{ $item['currency']}}{{$shippings[1]['price'] or ''}}</td>

                            </tr>
                        </tbody>
                    </table>
                </div>
    		</div>

            <button class="btn btn-primary btn-lg btn-block" data-toggle="modal" data-target="#myModal">Buy now {{ $item['currency']}}{{$item['price']}}</button>
</div>
            @if(Auth::check())

            <div class="modal fade" id="myModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h3 class="modal-title">{{{ $title }}}</h3>
                        </div>
                        <div class="modal-body">
                            {{ Form::open(['action' => array('BuyController@store', $item['id']), 'class' => 'form-horizontal', 'autocomplete' => 'off', 'validate', 'validate']) }}

                            <h4>Your Details</h4>
                            <div class="row">
                            <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12 form-group">
                                <div class="col-lg-8 col-sm-8 col-md-8">
                                    <img src="{{ $profileURL }}" class="img-responsive img-circle pull-left">
                                    <div class="buyer-info">
                                        <p class="h5">{{{ Auth::user()->name }}}</p>
                                        <p class="h5">{{{ Auth::user()->email }}}</p>

                                    </div>

                                </div>

                            </div>
                            </div>


                        <div class="row">
                            <h4>Where should it be shipped?</h4>

                            <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <input type="text" placeholder="Shipping address" class="form-control" id="autocomplete" autocomplete="false" autocomplete="off">
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




                        </div>


                        </div>
                        <div class="modal-footer">
                            <span class="h4 pull-left text-primary"> Total price (including shipping) </span><span class="h4 pull-right text-primary" id="total-price">&hellip;</span>
                            <button value="submit" class="btn btn-lg btn-block paypal-btn pull-right" disabled="disabled" id="checkout-btn">Checkout with Paypal</button>
                            {{ Form::close() }}
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->

        <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places"></script>
@else
<div class="modal fade" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title">Please Login or Register to Buy</h3>
            </div>
            <div class="modal-body">
                @include('parts.not_logged_in')
            </div>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@endif

	@stop