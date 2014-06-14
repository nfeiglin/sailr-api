@extends('layout.settings.main')
@section('head')
<link rel="stylesheet" href="{{ URL::asset('js/card/css/card.css') }}">
<script src="{{ URL::asset('js/controllers/billing/billingController.js') }}"></script>
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script>
    var cardType = '{{ $cardType or '' }}';
    var last4 = '{{ $last4 or '' }}'

</script>
<script>
    Stripe.setPublishableKey('{{ Config::get("stripe.sandbox.publishable") }}');
</script>

@stop

@section('content')
<div class="billingSettings" ng-controller="billingController">
    <h2>Billing</h2>
    <p>You can update your credit card and view your invoices here</p>
    <div class="panel col-md-8">
        <h3>Credit card</h3>

        <div class="row">
            <div class="updateCard col-sm-12 col-lg-12 col-md-12 col-xs-12" ng-show="showUpdateCard">

                <div class="row" style="margin-bottom: 25px;">
                    <div class="card-wrapper"></div>
                </div>

                <p class="text-primary">Please enter the details of the new credit card here</p>
                <div class="row">
                    <form id="cardForm" name="cardForm" ng-submit="updateCard()" class="form-horizontal panel"
                          novalidate="novalidate">
                        <input hidden="hidden" id="ZZZusersName" value="{{{ $user->name or '' }}}">

                        <div class="form-group">
                            <div class="col-sm-12 col-lg-12 col-md-12">
                                <input ng-model="card.number" id="cardNumber" class="form-control" type="text"
                                       maxlength="25" placeholder="Card number" name="number" required="required"
                                       name="cardNumber">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-4 col-lg-4 col-md-4">
                                <input ng-model="card.expiry" name="expiry" id="cardExpiry" placeholder="MM/YY"
                                       class="form-control" required="required" maxlength="9">
                            </div>
                            <div class="col-sm-4 col-lg-4 col-md-4">
                                <input ng-model="card.cvc" name="cvc" id="cardCVC" placeholder="CVC"
                                       class="form-control" maxlength="4" required="required" name="cvc">
                            </div>
                            <div class="col-sm-4 col-lg-4 col-md-4">
                                <button type="submit" class="btn btn-block btn-purple form-control" ng-disabled="cardForm.$invalid" ng-if="!posting">
                                    <span class="glyphicon glyphicon-credit-card"></span> Update card
                                </button>
                                <div class="dots" ng-if="posting">Updating...</div>
                            </div>
                        </div>

                    </form>
                </div>

            </div>

        </div>


        <table class="table">
            <tbody>
            <tr class="well">
                <td>
                    <div class="card-preview" ng.model="card.last4">
                        &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; @{{ card.last4 }}
                    </div>
                </td>
                <td>
                    <div class="card-type" ng-model="card.type">@{{ card.type }}</div>
                </td>
                <td>
                    <a href="#" class="btn btn-sm btn-purple" ng-click="showUpdateCard = !showUpdateCard;"><span class="glyphicon glyphicon-credit-card"></span> Edit card</a>
                </td>
            </tr>
            </tbody>

        </table>

    </div>

</div>

@stop

@section('bottom')
<script src="{{ URL::asset('js/card/js/card.js') }}"></script>
<script>

    $('#cardForm').card({
        // a selector or jQuery object for the container
        // where you want the card to appear
        container: '.card-wrapper', // *required*
        numberInput: 'input#cardNumber', // optional — default input[name="number"]
        expiryInput: 'input#cardExpiry', // optional — default input[name="expiry"]
        cvcInput: 'input#cardCVC', // optional — default input[name="cvc"]
        nameInput: 'input#ZZZusersName', // optional - defaults input[name="name"]

        width: 360, // optional — default 350px
        formatting: true // optional - default true
    });
</script>
@stop