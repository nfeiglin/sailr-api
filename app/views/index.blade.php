@extends('layout.main')
@section('head')

@include('parts.home.inline-css')

@stop

@section('full-width-top')
<div id="headerwrap">
    <div class="container">
        <div class="col-lg-12 col-xs-12 col-sm-12 text-white text-center">
            <h1>Buy, sell, and discover <span id="changeWord">fashion</span></h1>
            <div class="h3 head-subtitle">Join the community and follow your favourite creators</div>
            <div class="col-lg-4 col-md-4 col-md-offset-4 col-lg-offset-4 center-middle">
                <a href="{{ URL::action('UsersController@create') }}" class="btn btn-lg btn-block btn-blue cta-btn btn-big">Sign up</a>
            </div>

        </div>


    </div>

    </div>
@stop

@section('content')


<!-- /container -->
</div><!-- /headerwrap -->

<div data-ng-controller="homeController">
    <div class="container">
        <div class="row mt centered">
            <div class="col-lg-6 col-lg-offset-3">
                <h1>Create an online store with Sailr</h1>
                <h3 class="subtitle"><small>Sell, buy, and follow your favourite creators.</small></h3>

                <h3>The intersection of a social network and Ecommerce marketplace is here.</h3>
            </div>
        </div>
        <!-- /row -->
        <div class="row mt centered">
            <div class="col-lg-4">
                <span class="glyphicon glyphicon-tags big-font"></span>
                <h4>Sell simply</h4>

                <p>Sell with ease and update your listings with our beautiful product listing editor.</p>
            </div>
            <!--/col-lg-4 -->
            <div class="col-lg-4">
                <span class="glyphicon glyphicon-user big-font"></span>
                <h4>Follow creators and friends</h4>

                <p>Follow your friends, favourite brands, shops, and creators. See the latest things they are selling in
                    your feed. Grow your online shop through social engagement. </p>
            </div>
            <!--/col-lg-4 -->

            <div class="col-lg-4">
                <span class="glyphicon glyphicon-leaf big-font"></span>
                <h4>All you need, nothing else</h4>

                <p>Sailr offers everything you need to quickly and easily sell. No unnecessary menus or options.</p>
            </div>
            <!--/col-lg-4 -->


        </div>
        <!-- /row -->
    </div>
    <!-- /container -->

    <div class="container">
        <div class="row centered">
            <div class="col-lg-6 col-lg-offset-3">
                <p>
                    <a href="{{ URL::to('/ANTdesign') }}">Click here</a> to see a real Sailr store in action
                </p>
            </div>
        </div>

    <div class="container">
        <hr>
        <div class="row centered">
            <div class="col-lg-6 col-lg-offset-3">
                <a href="{{ URL::action('UsersController@create') }}" class="btn btn-lg btn-block btn-blue btn-big">Sign up</a>
            </div>

        </div>
        <div class="col-lg-3"></div>
    </div>
    <!-- /row -->
    <hr>


    <div class="container" id="pricing">
        <div class="row mt centered">
            <div class="col-lg-6 col-lg-offset-3">
                <h1>Our pricing.</h1>
            </div>
        </div>
        <!-- /row -->
        <div class="row mt centered">
            <div class="col-lg-6">
                <span class="glyphicon glyphicon-cloud big-font"></span>
                <h4>Free</h4>
                <p>Best suited to people browsing, buying and occasionally selling.</p>
                <ul class="list-unstyled">
                    <li>Limited to 4 listings per month</li>
                    <li>Maximum sale&nbsp;price of&nbsp;$40 including shipping</li>
                    <li>Buy and sell on your computer and mobile devices</li>
                    <li>Manage your sales</li>
                    <li>Get paid straight to your Paypal account</li>
                    <li>Buy from other sellers</li>
                    <li>No Sailr fees</li>

                </ul>
            </div>
            <!--/col-lg-4 -->
            <div class="col-lg-6 well well-lg">
                <span class="glyphicon glyphicon-tower big-font"></span>

                <h3>Awesome</h3>
                <h4>$12.99 /month</h4>

                <p class="subtitle">Excellent for sellers wanting freedom and flexibility.</p>
                <ul class="list-unstyled">
                    <li><strong>Unlimited</strong> listings per month</li>
                    <li><strong>Unlimited</strong> sale price</li>
                    <li>Buy and sell on your computer and mobile devices</li>
                    <li>Manage your sales</li>
                    <li>Get paid straight into your PayPal account</li>
                    <li>Buy from other sellers</li>
                    <li>No Sailr fees</li>
                </ul>

            </div>
            <!--/col-lg-4 -->
        </div>
        <!-- /row -->
    </div>
    <!-- /container -->

    <div class="col-lg-6 col-lg-offset-3">
        <h1>Recently added products</h1>
    </div>
    <div class="row mt centered">

        <sailr-recent-products sailr-number-of-products="@{{ numberOfProducts }}" sailr-offset-by="@{{ offsetLoadProducts }}">

        </sailr-recent-products>
        <button class="btn btn-sm btn-purple" ng-click="loadMore(3)">Load more...</button>
        <p class="ng-cloak help-block text-primary" ng-if="showNowSignupText">Seen enough products? <a href="{{ URL::action('UsersController@create') }}">Sign up now.</a></p>

    </div>
</div>

<div class="container">
    <hr>
    <div class="row centered">
        <div class="col-lg-6 col-lg-offset-3">
            <a href="{{ URL::action('UsersController@create') }}" class="btn btn-lg btn-block  btn-blue btn-big">Sign
                up!</a>
        </div>
        <!--End mc_embed_signup-->
    </div>
</div>
<!-- /row -->
<hr>

<p class="centered hidden">Based on BlackTie.co website - Attribution License 3.0 - 2013</p>
<p class="centered">Prices subject to change. Contact founders@sailr.co with any questions, feedback or comments.</p>
</div>


@stop