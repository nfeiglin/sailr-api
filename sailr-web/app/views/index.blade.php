@extends('layout.main')

@section('head')
<link href="{{ URL::asset('css/home.css') }}" rel="stylesheet">
@stop

@section('full-width-top')
<div id="headerwrap">
    <div class="container">
        <div class="col-lg-8">
            <h1>A big tag line that explains everything goes here...</h1>
            <p class="subtitle">With a subtitle possibly with a <a href="#">link.</a> </p>
        </div>

        <div class="col-lg-4 btn-container">
            <a href="{{ URL::action('UsersController@create') }}" class="btn btn-lg btn-block btn-turq cta-btn btn-big">Sign up!</a>
        </div>

    </div>

    {{-- <img class="img-responsive centered" src="http://sailr.co/img/Sailr-logo-500.png" alt="Sailr logo" style="margin-bottom: 35px;"> --}}

</div>
@stop

@section('content')


<!-- /container -->
</div><!-- /headerwrap -->
<div class="container">
    <div class="row mt centered">
        <div class="col-lg-6 col-lg-offset-3">
            <h1>Buy and sell online and on your smartphone with Sailr</h1>
            <h3>The intersection of a social network and Ecommerce marketplace is launching soon.</h3>
        </div>
    </div>
    <!-- /row -->
    <div class="row mt centered">
        <div class="col-lg-4">
            <span class="glyphicon glyphicon-tags" style="font-size: 80px;"></span>
            <h4>1 - Sell simply</h4>
            <p>Sell and manage sales with the ease you are used to with your favourite social networks. On iPhone or on our website</p>
        </div>
        <!--/col-lg-4 -->
        <div class="col-lg-4">
            <span class="glyphicon glyphicon-user" style="font-size: 80px;"></span>
            <h4>2 - Follow creators and friends</h4>
            <p>Follow your favourite brands, shops and creators to see the latest things they are selling in your stream. Follow your friends to see what they are buying and selling in your feed, too!</p>
        </div>
        <!--/col-lg-4 -->
        <div class="col-lg-4">
            <span class="glyphicon glyphicon-star" style="font-size: 80px;"></span>
            <h4>3 - Reach for the stars</h4>
            <p>While you reach for the stars with you Ecommerce business on Sailr, we give you a way to directly reach your customers as they follow you to see the latest items for sale in their stream.</p>
        </div>
        <!--/col-lg-4 -->
    </div>
    <!-- /row -->
</div>
<!-- /container -->
<div class="container">
    <hr>
    <div class="row centered">
        <div class="col-lg-6 col-lg-offset-3">
            <a href="{{ URL::action('UsersController@create') }}" class="btn btn-lg btn-block btn-turq btn-big">Sign up!</a>
        </div>
        <!--End mc_embed_signup-->
    </div>
    <div class="col-lg-3"></div>
</div>
<!-- /row -->
<hr>
</div>

<div class="container" id="pricing">
    <div class="row mt centered">
        <div class="col-lg-6 col-lg-offset-3">
            <h1>Our pricing. It's for everyone.</h1>
            <h3>Only pay when things get serious.</h3>
        </div>
    </div>
    <!-- /row -->
    <div class="row mt centered">
        <div class="col-lg-6">
            <span class="glyphicon glyphicon-cloud" style="font-size: 80px;"></span>
            <h4>Free</h4>
            <p>Best suited to people browsing, buying and occasionally selling.</p>
            <ul class="list-unstyled">
                <li>Post up to 4 listings per month</li>
                <li>Maximum sale&nbsp;price of&nbsp;$40 including shipping</li>
                <li>Buy and sell on iPhone and online</li>
                <li>Manage your sales</li>
                <li>Get paid straight to your Paypal account</li>
                <li>Buy from other sellers</li>
                <li>No Sailr fees</li>

            </ul>
        </div>
        <!--/col-lg-4 -->
        <div class="col-lg-6 well well-lg">
            <span class="glyphicon glyphicon-tower" style="font-size: 80px;"></span>
            <h3>Awesome</h3>
            <h4>$12.99 /month</h4>
            <p class="subtitle">Excellent for sellers wanting freedom and flexibility.</p>
            <ul class="list-unstyled">
                <li><strong>Unlimited</strong> listings per month</li>
                <li><strong>Unlimited</strong> sale price</li>
                <li>Buy and sell on iPhone and online</li>
                <li>Manage your sales</li>
                <li>Customised header image on your page</li>
                <li>Get paid straight to your Paypal account</li>
                <li>Buy from other sellers</li>
                <li>No Sailr fees</li>
            </ul>

        </div>
        <!--/col-lg-4 -->
    </div>
    <!-- /row -->
</div>
<!-- /container -->
<div class="container">
    <hr>
    <div class="row centered">
        <div class="col-lg-6 col-lg-offset-3">
            <a href="{{ URL::action('UsersController@create') }}" class="btn btn-lg btn-block btn-turq btn-big">Sign up!</a>
        </div>
        <!--End mc_embed_signup-->
    </div>
</div>
<!-- /row -->
<hr>
<p class="centered hidden">Based on BlackTie.co website - Attribution License 3.0 - 2013</p>
<p class="centered">Prices subject to change. Contact founders@sailr.co with any questions, feedback or comments.</p>
</div>
<!-- /container -->

@stop