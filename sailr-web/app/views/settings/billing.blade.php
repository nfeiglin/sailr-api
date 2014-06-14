@extends('layout.settings.main')
@section('head')
<link rel="stylesheet" href="{{ URL::asset('js/card/css/card.css') }}">
<script src="{{ URL::asset('js/controllers/billing/billingController.js') }}"></script>
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>

@stop

@section('content')
<div class="billingSettings" ng-controller="billingController">
    <h2>Billing</h2>

    <div class="panel col-md-8">
        <h3>Credit card</h3>
        <div class="row">
            <div class="updateCard col-sm-12 col-lg-12 col-md-12 col-xs-12" ng-show="showUpdateCard">

                <div class="row" style="margin-bottom: 25px;">
                    <div class="card-wrapper"></div>
                </div>

                <div class="row">
                    <form id="cardForm" name="cardForm" ng-submit="updateCard()" class="form-horizontal panel">
                        <input hidden="hidden" id="ZZZusersName" value="{{{ $user->name or '' }}}">
                        
                        <div class="form-group">
                            <div class="col-sm-12 col-lg-12 col-md-12">
                                <input id="cardNumber" class="form-control" type="text" maxlength="16" placeholder="Card number" name="number">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-4 col-lg-4 col-md-4">
                                <input name="expiry" id="cardExpiry" placeholder="MM/YY" class="form-control">
                            </div>
                            <div class="col-sm-4 col-lg-4 col-md-4">
                                <input name="cvc" id="cardCVC" placeholder="CVC" class="form-control">
                            </div>
                            <div class="col-sm-4 col-lg-4 col-md-4">
                                <input type="submit" value="Update card" class="btn btn-block btn-turq form-control" ng-disabled="cardForm.$invalid">
                            </div>
                        </div>

                    </form>
                </div>

            </div>

        </div>


        <table class="table table-bordered">
            <tbody>
            <tr class="well">
                <td>
                    <div class="card-preview">&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; {{ $last4 }}</div>
                </td>
                <td>
                    <div class="card-type">{{ $cardType }}</div>
                </td>
                <td>
                    <a href="#" class="btn btn-sm btn-purple" ng-click="showUpdateCard = !showUpdateCard;">Edit</a>
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