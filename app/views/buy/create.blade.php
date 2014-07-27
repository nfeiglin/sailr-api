@extends('layout.simple')
    @section('head')
<script>
     var item = {{ json_encode($item) }};
     item.ship_price = parseFloat(item.ship_price);
</script>
    @stop

	@section('content')

		<div class="form-signin wide panel">
			<h2>{{{ $item['title'] }}} <small class="text-danger">{{ $item['currency']}}{{$item['price'] }}</small></h2>

            <div class="item-user panel panel-default" id="buy-user">
                <a href="{{ action('UsersController@show', $item['user']['username']) }}"><img src="{{ $item['user']['profile_img']['url'] }}" class="item-user-img img-circle" width="64vw" height="64vw"></a>
                <a href="{{ action('UsersController@show', $item['user']['username']) }}" class="h4 name">{{ $item['user']['name'] }} </a> <a href="{{ action('UsersController@show', $item['user']['username']) }}"class="h5 username">{{ '@' . $item['user']['username'] }}</a>
            </div>

            <button class="btn btn-blue btn-lg btn-blue btn-big btn-block" data-toggle="modal" data-target="#buyModal">Buy now {{ $item['currency']}}{{$item['price']}}</button>
            <p class="ships_to">Ships to {{ CountryHelpers::getCountryNameFromISOCode($item['ships_to']) }}</p>

			<div class="item buy-page" id="{{ $item['id'] }}">

        			<div class="img-gallery">
						@foreach($item['photos'] as $photo)
      						<div class="gallery-item">
      							<img draggable="false" src=" {{ $photo['url'] }}" class="img-responsive" alt="Photo of {{{ $item['title'] }}}">
      						</div>
      					@endforeach
      				</div>

                <div class="caption" sailr-entity-link>
                   {{ $item['description'] or '' }}
                </div>

                <h4>Price</h4>

                <div class="table col-md-6">
                    @include('parts.shippingTable')
                </div>


    		</div>

            <button class="btn btn-blue btn-lg btn-block" data-toggle="modal" data-target="#buyModal">Buy now {{ $item['currency']}}{{$item['price']}}</button>
            <hr>

            <sailr-comments sailr-product-id="{{ $item['id'] }}"></sailr-comments>

            <div class="share-buttons">
                <div class="addthis_native_toolbox"></div>
            </div>

        </div>
            @if(Auth::check())

            <div class="modal fade" id="buyModal">
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
                                    <div class="buyer-info">
                                        <img ng-src="@{{ loggedInUser.profile_img[0].url }}" class="img-responsive img-circle pull-left" width="50wv">
                                        <p class="h5">{{{ Auth::user()->name }}}</p>
                                        <p class="h5">{{{ Auth::user()->email }}}</p>

                                    </div>

                                </div>

                            </div>
                            </div>


                        <div class="row">
                            <h4>Where should it be shipped?</h4>

                            <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <input type="text" placeholder="Shipping address" class="form-control" id="autocomplete" autocomplete="false" autocomplete="off" required="required">
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

                            @include('parts.shippingTable')
                        </div>


                        </div>
                        <div class="modal-footer">
                            <button value="submit" class="btn btn-lg paypal-btn pull-right" id="checkout-btn">Checkout with Paypal</button>
                            {{ Form::close() }}
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->

        <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places"></script>
@else
<div class="modal fade" id="buyModal">
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