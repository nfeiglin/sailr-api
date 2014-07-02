@extends('layout.profile.main')
@section('below')




{{--
<div class="col-sm-4 col-lg-4 col-md-4">
    <div class="thumbnail">
        <img src="http://placehold.it/320x120" alt="">
        <div class="caption">
            <h4 class="pull-right">$55.00</h4>
            <h4><a href="http://laravel-shop.gopagoda.com/products/2">Second product</a></h4>
            <p>This is a short description</p>
        </div>
        <div class="ratings">
            <p class="pull-right">269 reviews</p>
            <p>
                <span class="glyphicon glyphicon-star"></span>
                <span class="glyphicon glyphicon-star"></span>
                <span class="glyphicon glyphicon-star"></span>
                <span class="glyphicon glyphicon-star-empty"></span>
                <span class="glyphicon glyphicon-star-empty"></span>
            </p>
        </div>
    </div>
</div>
--}}

<div class="row">
    @foreach($items as $item)
    <div class="col-sm-6 col-lg-6 col-md-6">
        <div class="item" id="{{ $item['id'] }}">

            <div class="thumbnail">
                <div class="img-gallery">
                    @foreach($item['photos'] as $photo)
                    <div class="gallery-item">
                        <img draggable="false" src="{{ $photo['url'] }}" class="img-responsive" alt="Preview image for {{{ $item['title'] }}}">
                    </div>
                    @endforeach
                </div>

                <div class="caption">
                    <h4 class="pull-right">{{$item['currency']}}{{$item['price']}}</h4>
                    <a href="{{ action('BuyController@create', [$item['user']['username'], $item['id']]) }}"><h4>{{{ $item['title'] }}}</h4></a>
                </div>

            </div>

        </div>
    </div>
    @endforeach
</div>



{{ $paginator->links() }}


@stop