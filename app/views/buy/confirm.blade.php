@extends('layout.simple')

@section('content')

<form class="form-signin panel" method="post" action="{{ action('BuyController@doConfirm', $id) }}">
    {{ Form::token() }}
    <input type="hidden" value="{{ $pp_token }}" name="pp_token">
        <h2>Confirm purchase</h2>
        You are buying from <a class="text-muted" href="{{ action('UsersController@show', $item['user']['username']) }}">{{{
            $item['user']['name'] }}} {{ '@' . $item['user']['username'] }}</a>


        <div class="item buy-page" id="{{ $item['id'] }}">

            <div class="purchase-item well">

                <img draggable="false" src=" {{ $item['photos'][0]['url'] }}" class="confirm-item-thumbnail" alt="Item image">
                {{{ $item['title'] }}}
            </div>
        <h3>Shipping details</h3>
            <p>{{{ $address->getShipToName() }}}</p>
            <p>{{{ $address->getAddress1() }}}</p>
            <p>{{{ $address->getAddress2() }}}</p>
            <p>{{{ $address->getCity() }}}, {{{ $address->getState() }}}</p>
            <p>{{{ $address->getCountry() }}}</p>
            <p>{{{ $address->getZipCode() }}}</p>

        <h3>Payment details</h3>
        <p>Item price: {{ $payment[0]->ItemTotal->currencyID }}{{ $payment[0]->ItemTotal->value }}</p>
        <p>Shipping price: {{ $payment[0]->ShippingTotal->currencyID }}{{ $payment[0]->ShippingTotal->value }}</p>
        <p>Total: {{ $payment[0]->OrderTotal->currencyID }}{{ $payment[0]->OrderTotal->value }}</p>

        </div>

        <button class="btn btn-primary btn-lg btn-block" value="submit">Confirm purchase {{ $payment[0]->OrderTotal->currencyID }}{{ $payment[0]->OrderTotal->value }}
        </button>
    <a href="{{ URL::to('/') }}" class="text-muted small cancel-purchase">Cancel. Take me home.</a>
</form>

@stop