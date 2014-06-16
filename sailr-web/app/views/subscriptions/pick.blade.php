@extends('layout.barebones')

@section('head')

@stop

@section('body')
<div class="content">
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

                        <a href="#" class="btn btn-link">Continue &rarr;</a>
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

                        <div class="row" style="margin-bottom: 25px;">
                            <div class="hide-name">
                                <div class="card-wrapper"></div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="updateCard col-sm-12 col-lg-12 col-md-12 col-xs-12">
                                <div class="row">
                                    <form id="cardForm" name="cardForm" ng-submit="updateCard()"
                                          class="form-horizontal" novalidate="novalidate">

                                        <div class="form-group">
                                            <div class="col-sm-12 col-lg-10 col-lg-offset-1 col-md-12">
                                                <input ng-model="card.number" id="cardNumber" class="form-control"
                                                       type="text" maxlength="25" placeholder="Card number" name="number"
                                                       required="required" id="cardNumber" focus-me="showUpdateCard">
                                            </div>

                                        </div>


                                        <div class="form-group">
                                            <div class="col-sm-6 col-lg-6 col-lg-push-1 col-md-6">
                                                <input ng-model="card.expiry" name="expiry" id="cardExpiry"
                                                       placeholder="MM/YY"
                                                       class="form-control" required="required" maxlength="9">
                                            </div>
                                            <div class="col-sm-6 col-lg-4 col-lg-offset-1 col-md-6">
                                                <input ng-model="card.cvc" name="cvc" id="cardCVC" placeholder="CVC"
                                                       class="form-control" maxlength="4" required="required"
                                                       name="cvc">
                                            </div>

                                        </div>
                                        <div class="form-group col-lg-8 col-lg-push-1 stripe-form-badge">
                                            <a href="https://stripe.com" target="_blank">
                                                <img class="pull-left" draggable="false" src="{{ URL::asset('images/stripe-dark-sm.png') }}">
                                            </a>
                                        </div>

                                    </form>
                                </div>

                            </div>
                            <input type="hidden" value="{{ $user->name or ''}}" id="initName">
                        </div>

                        <a href="#" class="btn btn-lg btn-turq btn-md-long" ng-click="showForm"
                           ng-disabled="cardForm.$invalid && showForm" ng-if="!posting || showForm">Start Awesome
                            ($12.99/month)</a>
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

<script>

    var cardPreview = $('#cardForm').card({
        // a selector or jQuery object for the container
        // where you want the card to appear
        container: '.card-wrapper', // *required*
        numberInput: 'input#cardNumber', // optional — default input[name="number"]
        expiryInput: 'input#cardExpiry', // optional — default input[name="expiry"]
        cvcInput: 'input#cardCVC', // optional — default input[name="cvc"]
        nameInput: 'input#cardName', // optional - defaults input[name="name"]

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
