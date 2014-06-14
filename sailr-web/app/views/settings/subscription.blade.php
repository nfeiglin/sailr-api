@extends('layout.settings.main')
@section('content')

<h2>Subscription</h2>
    {{-- @if($user->subscribed())
        {{ print_r($user->subscription(), 1) }}
    @else --}}
    <h4>Get Awesome</h4>
    <p>Reasons why you should upgrade go here...</p>

    {{ Form::token() }}
    <form action="{{ URL::action('SubscriptionsController@store') }}" method="POST">
        {{ Form::token() }}
        <script
            src="https://checkout.stripe.com/checkout.js"
            class="stripe-button"
            data-key="{{ Config::get('stripe.sandbox.publishable') }}"
            data-image="/images/logo-500.png"
            data-name="Sailr"
            data-currency="AUD"
            data-email="{{ $user->email }}"
            data-description="Awesome Plan (Monthly)"
            data-panel-label="Subscribe"
            data-label="Subscribe"
            data-amount="1299">
        </script>

    </form>

    <a href="#" class="btn btn-lg btn-big btn-turq">Upgrade to Awesome (AUD12.99 / month)</a>
   {{-- @endif --}}
</div>
@stop