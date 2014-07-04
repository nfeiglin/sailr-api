@extends('layout.barebones')

@section('head')
<script src="{{ URL::asset('js/controllers/subscription/chooseController.js') }}"></script>
@stop

@section('body')

@if(Session::has('message'))<div class="alert alert-warning">{{ Session::get('message') }}</div> @endif
@if(Session::has('success'))<div class="alert alert-success">{{ Session::get('success') }}</div> @endif
@if(Session::has('error'))<div class="alert alert-warning">{{ Session::get('error') }}</div> @endif
@if(Session::has('fail'))<div class="alert alert-danger">{{ Session::get('fail') }}</div> @endif

<div class="content" ng-controller="chooseController">
    <div class="row">
        <div class="fat-bar">
            <div class="container">
                <img src="{{ URL::asset('images/logo.png') }}" class="img-responsive" height="100px" width="100px">
            </div>
        </div>
        <div class="container">
            <div class="text-center">
                <div class="page-header">
                    <h1>Welcome to Sailr</h1>

                    <h3 class="text-muted">Tell us, what suits you best?</h3>
                </div>
            </div>

            <div class="plan-choice personal col-xs-12 col-sm-12 col-md-6 col-lg-6 text-center">
                <div class="panel panel-default">
                    <div class="plan-choice-icon">
                        <span class="big-font glyphicon glyphicon-user"></span>
                    </div>

                    <h3>Free</h3>

                    <p class="h5 subtitle">Best suited to people browsing, buying and occasionally selling.</p>

                    <div class="panel-body">

                        <ul class="list-unstyled">
                            <li>Post up to 4 listings per month</li>
                            <li>Maximum sale&nbsp;price of&nbsp;$40 including shipping</li>
                            <li>Buy and sell on iPhone and online</li>
                            <li>Get notified when you sell</li>
                            <li>Get paid straight to your PayPal account</li>
                            <li>Buy from other sellers</li>
                            <li>No Sailr fees</li>

                        </ul>

                        <a href="{{ URL::to('') }}" class="btn btn-link">Continue &rarr;</a>
                    </div>
                </div>

            </div>

            <div class="plan-choice awesome col-xs-12 col-sm-12 col-md-6 col-lg-6 text-center">
                <div class="panel panel-default">
                    <div class="plan-choice-icon">
                        <span class="big-font glyphicon glyphicon-tower text-primary"></span>
                    </div>

                    <h3>Awesome</h3>

                    <p class="h5 subtitle">Excellent for sellers wanting freedom and flexibility.</p>

                    <div class="panel-body">

                        <ul class="list-unstyled">
                            <li>Add <strong>unlimited</strong> products to your store</li>
                            <li>Sell at <strong>any</strong> price you desire</li>
                            <li>Buy and sell online</li>
                            <li>Get notified when you sell</li>
                            <li>Get paid straight to your PayPal account</li>
                            <li>Buy from other sellers</li>
                            <li>No Sailr fees</li>
                        </ul>

                        <div class="row ng-cloak" id="cardFormContainer" ng-show="showCardForm" ng-animate="'animate'">
                            <div class="row hidden-xs" style="margin-bottom: 25px;">
                                <div class="hide-name">
                                    <div class="card-wrapper"></div>
                                </div>

                            </div>
                            <div class="row  ng-cloak">
                                <div class="updateCard col-sm-12 col-lg-12 col-md-12 col-xs-12">
                                    <div class="row">
                                        <form id="cardForm" name="cardForm" ng-submit="subscribeToPlan('awesome')" novalidate="novalidate">


                                                <div class="col-sm-12 col-lg-10 col-lg-offset-1 col-md-12">
                                                    <div class="form-group">
                                                        <input ng-model="card.number" id="cardNumber" class="form-control"
                                                               type="text" maxlength="25" placeholder="Card number"
                                                               required="required" id="cardNumber" focus-me="showUpdateCard">
                                                    </div>

                                            </div>



                                                <div class="col-sm-6 col-xs-6 col-lg-6 col-lg-push-1 col-md-6">
                                                    <div class="form-group">
                                                        <input ng-model="card.expiry" id="cardExpiry"
                                                               placeholder="MM/YY"
                                                               class="form-control" required="required" maxlength="9">
                                                    </div>

                                                </div>

                                                <div class="col-sm-6 col-xs-6 col-lg-4 col-lg-offset-1 col-md-6">
                                                    <div class="form-group">
                                                        <input ng-model="card.cvc" id="cardCVC" placeholder="CVC"
                                                               class="form-control" maxlength="4" required="required">
                                                    </div>

                                                </div>


                                            <div class="form-group col-lg-8 col-lg-push-1 stripe-form-badge">
                                                <a href="https://stripe.com" target="_blank">
                                                    <img class="pull-left" draggable="false" src="{{ URL::asset('images/stripe-dark-sm.png') }}">
                                                </a>
                                            </div>
                                            <div class="col-xs-12 col-lg-12">
                                                <div class="form-group">
                                                    <input ng-hide="posting" type="submit" value="Subscribe AU12.99/month" class="btn btn-lg btn-block btn-turq" ng-if="showingCreditForm" ng-disabled="cardForm.$invalid && showingCreditForm">
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                </div>
                                <input type="hidden" value="{{ $user->name or ''}}" id="initName">
                            </div>
                        </div>

                        <div class="col-sm-6 col-sm-offset-3  ng-cloak">
                            <div class="form-group">
                                <a class="small text-muted" ng-click="toggleCouponShow()">Have a coupon code?</a>
                                <div class="couponCode" ng-show="showCoupon">
                                    <input type="text" ng-model="couponCode" class="form-control" placeholder="Coupon code...">
                                </div>
                            </div>
                        </div>


                        <button class="btn btn-lg btn-turq btn-md-long ng-cloak" ng-click="handleSubscribeButtonPressed()" ng-if="!showingCreditForm">
                            Start Awesome ($12.99/month)
                        </button>
                        <p class="small text-muted ng-cloak">The plan will auto-renew until canceled.</p>
                        <div class="dots ng-cloak" ng-if="posting">Subscribing...</div>


                    </div>
                </div>
            </div>



        </div>
    </div>

    <div class="jumbotron purpleBackground bottom you-should-know">
        <div class="text-center">
            <h2 class="text-white">What's yours is yours. </h2>

            <p class="h4 text-white">We're not ones for taking sneaky cuts of your sales.</p>
        </div>

    </div>

</div>
<input id="zzzCardName" type="hidden" hidden="hidden">
<script>

    var cardPreview = $('#cardForm').card({
        // a selector or jQuery object for the container
        // where you want the card to appear
        container: '.card-wrapper', // *required*
        numberInput: 'input#cardNumber', // optional — default input[name="number"]
        expiryInput: 'input#cardExpiry', // optional — default input[name="expiry"]
        cvcInput: 'input#cardCVC', // optional — default input[name="cvc"]
        nameInput: 'input#zzzCardName', // optional - defaults input[name="name"]

        width: 380, // optional — default 350px
        formatting: true // optional - default true
    });

    /*  if (usersName.length > 0) {
     $('input#cardName').val(usersName);
     $('input#cardName').trigger('change');
     }
     */
</script>

@stop
