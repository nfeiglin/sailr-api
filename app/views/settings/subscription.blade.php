@extends('layout.settings.main')

@section('head')
<script>
    var subscription = {{ $subscription }};
    var user = {{ $user }};
</script>
@stop
@section('content')

<div ng-controller="manageController">

    <div ng-if="subscription.id">
        <p>
            You're currently subscribed to the <b>@{{ subscription.name }}</b> plan.
        </p>

        <p ng-if="!subscription.cancel_at_period_end">
            It costs <b>@{{ subscription.formatted_amount }}</b> each @{{ subscription.interval }} and will auto-renew on @{{ subscription.formatted_period_end }}.
        </p>

        <p ng-if="subscription.cancel_at_period_end">
            Your subscription has been canceled and you won't be billed again until you resubscribe. You have some time remaining on this plan and can continue to enjoy its features until @{{ subscription.formatted_period_end }}.
        </p>

        </p>

        <p>
            You may view your invoices and change the credit card on your account in your <a ng-href="@{{ baseURL + '/settings/billing'}}">billing settings.</a>
        </p>

        <br>
        <br>
        <hr>

        <div ng-if="!posting">
            <div ng-if="!subscription.cancel_at_period_end">
                <p class="text-muted small pull-right">
                   <a href="#" class="btn btn-default btn-sm" ng-click="cancelSubscription()">cancel subscription</a>
                </p>
            </div>

        </div>

        <div ng-if="posting" class="pull-right">
            <div class="dots">
                Unsubscribing...
            </div>
        </div>

    </div>

    <div ng-if="!subscription.id">
        <p>
            Hi @{{ user.name }}, I see you're not subscribed. It's a shame that you aren't experiencing all that our <b>Awesome</b> plan can offer.
        </p>
        <p>
            With Awesome, you can sell at any price and as add as many products as you want to your store. Don't stay limited, be Awesome.
        </p>

        <p>
            <a ng-href="@{{ baseURL + '/plans/choose' }}" class="btn btn-turq btn-md">Get Awesome</a>
        </p>

    </div>
</div>

@stop