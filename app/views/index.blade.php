@extends('layout.main')

@section('head')
<link href="{{ URL::asset('css/home.css') }}" rel="stylesheet">
@stop

@section('full-width-top')
<div id="headerwrap">
    <div class="container">
        <div class="col-lg-8 col-xs-12 col-sm-12 text-white">
            <h1>Make an online store in seconds</h1>
            <h2 class="text-white">1. Create an account</h2>
            <h2 class="text-white">2. Add products to your store</h2>
            <h2 class="text-white">3. Get paid direct to your PayPal</h2>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 col-sm-4 btn-container pull-left">
            <a href="{{ URL::action('UsersController@create') }}" class="btn btn-lg btn-block btn-turq cta-btn btn-big text-center">Sign up!</a>
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
            <h1>Buy and sell online and with Sailr</h1>
            <h3>The intersection of a social network and Ecommerce marketplace is here.</h3>
        </div>
    </div>
    <!-- /row -->
    <div class="row mt centered">
        <div class="col-lg-4">
            <span class="glyphicon glyphicon-tags" style="font-size: 80px;"></span>
            <h4>Sell simply</h4>
            <p>Sell with ease and update your listings with our beautiful product listing editor.</p>
        </div>
        <!--/col-lg-4 -->
        <div class="col-lg-4">
            <span class="glyphicon glyphicon-user" style="font-size: 80px;"></span>
            <h4>Follow creators and friends</h4>
            <p>Follow your friends, favourite brands, shops, and creators. See the latest things they are selling in your feed. Grow your online shop through social engagement. </p>
        </div>
        <!--/col-lg-4 -->

       <div class="col-lg-4">
            <span class="glyphicon glyphicon-leaf" style="font-size: 80px;"></span>
            <h4>All you need, nothing else</h4>
            <p>Sailr offers everything you need to quickly and easily sell. No unnecessary menus or options.</p>
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
            <a href="{{ URL::action('UsersController@create') }}" class="btn btn-lg btn-block btn-turq btn-big">&#10141; Sign up</a>
        </div>

    </div>
    <div class="col-lg-3"></div>
</div>
<!-- /row -->
<hr>
</div>

<div class="container" id="pricing">
    <div class="row mt centered">
        <div class="col-lg-6 col-lg-offset-3">
            <h1>Our pricing.</h1>
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