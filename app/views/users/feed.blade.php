@extends('layout.main')
	@section('content')

		<div class="col-lg-8 col-md-8 col-sm-8 col-sm-offset-2 col-lg-offset-2 col-md-offset-2 col-xs-12 well well-sm" data-ng-controller="feedContentController">

            <div class="row">
                <div class="col-xs-12 panel ng-cloak" style="margin-bottom: 20px">
                    <sailr-feed-onboard-box>

                    </sailr-feed-onboard-box>
                </div>
            </div>


			@foreach($items as $item)
			<div class="item" id="{{ $item['id'] }}">
				<div class="item-user panel panel-default">
						<a href="{{ action('UsersController@show', $item['user']['username']) }}"><img src="{{ $item['user']['profile_img'][0]['url'] or '' }}" class="item-user-img img-circle" width="64vw" height="64vw"></a>
						<a href="{{ action('UsersController@show', $item['user']['username']) }}" class="h4 name">{{ $item['user']['name'] }} </a> <a href="{{ action('UsersController@show', $item['user']['username']) }}"class="h5 username text-primary">{{ '@' . $item['user']['username'] }}</a>
				</div>

				<div class="thumbnail">
					<div class="caption">
        				<a href="{{ action('BuyController@create', [$item['user']['username'], $item['id']]) }}"><h3>{{{ $item['title'] }}}</h3></a>
        			</div>

        			<div class="img-gallery">
						@foreach($item['photos'] as $photo)
      						<div class="gallery-item">
      							<img draggable="false" src="{{ $photo['url'] }}" class="img-responsive" alt="...">
      						</div>
      					@endforeach
      				</div>

      				<div class="caption">
        				<a href="{{ action('BuyController@create', [$item['user']['username'], $item['id']]) }}" class="btn btn-blue btn-lg btn-block h3" role="button">Buy now for {{$item['currency']}}{{$item['price']}}</a>
      				</div>

    			</div>

                <sailr-comments sailr-product-id="{{ $item['id'] }}">
                </sailr-comments>

    		</div>
			@endforeach

            <div class="row">
                <hr>
                <div class="col-xs-12 ng-cloak">
                    <h2>Recently added products</h2>
                    <p class="subtitle">These are recently added products from everyone on Sailr</p>
                </div>

                <sailr-recent-products sailr-number-of-products="@{{ numberOfProducts }}" sailr-offset-by="@{{ offsetLoadProducts }}">
                </sailr-recent-products>
                <div class="col-xs-12 col-md-4 col-lg-4">
                    <button class="btn btn-block btn-sm btn-purple" ng-click="loadMoreRecentProducts(3)">Load more recent...</button>
                </div>
            <hr>
            </div>


		{{ $paginator->links() }}
    </div>

	@stop