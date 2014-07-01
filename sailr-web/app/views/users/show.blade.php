@extends('layout.profile.main')
@section('below')


@foreach($items as $item)
<div class="item" id="{{ $item['id'] }}">

    <div class="thumbnail">
        <div class="caption">
            <a href="{{ action('BuyController@create', [$item['user']['username'], $item['id']]) }}"><h3> {{{ $item['title'] }}} </h3></a>
        </div>

        <div class="img-gallery">
            @foreach($item['photos'] as $photo)
                <div class="gallery-item">
                    <img draggable="false" src="{{ $photo['url'] }}" class="img-responsive" alt="...">
                </div>
            @endforeach
        </div>

        <div class="caption">
            <a href="{{ action('BuyController@create', [$item['user']['username'], $item['id']]) }}" class="btn btn-primary btn-lg btn-block h3"
               role="button">Buy now for {{$item['currency']}}{{$item['price']}}</a>
        </div>

    </div>

</div>
@endforeach
{{ $paginator->links() }}


@stop