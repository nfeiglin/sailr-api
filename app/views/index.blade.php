@extends('layout.main')
@section('head')

<style>
    body{background-color:#f2f2f2;font-weight:300;font-size:16px;color:#555;-webkit-font-smoothing:antialiased;-webkit-overflow-scrolling:touch}h1,h2,h3,h4,h5,h6{font-weight:300;color:#333}h1{font-size:40px}h3{color:#95a5a6;font-weight:400}h4{color:#95a5a6;font-weight:400;font-size:20px}p{line-height:28px;margin-bottom:25px;font-size:16px}.centered{text-align:center}a{color:#3498db;word-wrap:break-word;-webkit-transition:color .1s ease-in,background .1s ease-in;-moz-transition:color .1s ease-in,background .1s ease-in;-ms-transition:color .1s ease-in,background .1s ease-in;-o-transition:color .1s ease-in,background .1s ease-in;transition:color .1s ease-in,background .1s ease-in}a:focus,a:hover{color:#7b7b7b;text-decoration:none;outline:0}a:after,a:before{-webkit-transition:color .1s ease-in,background .1s ease-in;-moz-transition:color .1s ease-in,background .1s ease-in;-ms-transition:color .1s ease-in,background .1s ease-in;-o-transition:color .1s ease-in,background .1s ease-in;transition:color .1s ease-in,background .1s ease-in}hr{display:block;height:1px;border:0;border-top:1px solid #ccc;margin:1em 0;padding:0}.mt{margin-top:40px;margin-bottom:40px}.form-control{height:42px;font-size:18px;width:280px}i{margin:8px;color:#3498db}#headerwrap{margin-top:-20px;padding-top:200px;min-height:650px;width:100%;background:#5856D6;background-image:url(../build/images/purple-bg-sm.jpg);background-repeat:no-repeat;background-size:cover;background-position:center center;filter:blur(13px)}#headerwrap h1{margin-top:60px;margin-bottom:15px;color:#fff;font-size:60px;font-weight:300;letter-spacing:1px}#headerwrap .subtitle{color:#fff;font-size:24px;line-height:2.5em}#headerwrap .btn-container{vertical-align:middle}img.centered{margin-left:auto;margin-right:auto}
</style>
@stop

@section('full-width-top')
<div id="headerwrap">
    <div class="container">
        <div class="col-lg-8 col-xs-12 col-sm-12 text-white">
            <h1>Make an online store in seconds</h1>
            <h2 class="text-white">1. Create an account</h2>
            <h2 class="text-white">2. Add products to your store</h2>
            <h2 class="text-white">3. Get paid directly to your PayPal</h2>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 col-sm-4 btn-container pull-left">
            <a href="{{ URL::action('UsersController@create') }}" class="btn btn-lg btn-block btn-turq cta-btn btn-big text-center">Sign up!</a>
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
                <span class="glyphicon glyphicon-tags" style="font-size: 80px;"></span>
                <h4>Sell simply</h4>

                <p>Sell with ease and update your listings with our beautiful product listing editor.</p>
            </div>
            <!--/col-lg-4 -->
            <div class="col-lg-4">
                <span class="glyphicon glyphicon-user" style="font-size: 80px;"></span>
                <h4>Follow creators and friends</h4>

                <p>Follow your friends, favourite brands, shops, and creators. See the latest things they are selling in
                    your feed. Grow your online shop through social engagement. </p>
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
                <a href="{{ URL::action('UsersController@create') }}" class="btn btn-lg btn-block btn-turq btn-big">Sign up</a>
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
                <span class="glyphicon glyphicon-cloud" style="font-size: 80px;"></span>
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
                <span class="glyphicon glyphicon-tower" style="font-size: 80px;"></span>

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
            <a href="{{ URL::action('UsersController@create') }}" class="btn btn-lg btn-block btn-turq btn-big">Sign
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