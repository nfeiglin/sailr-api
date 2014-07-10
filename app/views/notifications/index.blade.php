@extends('layout.main')

@section('head')
<script>
    var sailr = {
        notifications: {{ $notifications }}
    };
</script>

@stop

@section('content')

<div data-ng-controller="notificationsController">

    <div class="row">
        <p class="lead text-white" ng-if="notifications.length < 1">You have no notifications</p>
        <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12">
            <div class="panel">
                <div class="panel-body">
                    <div class="" ng-repeat="notification in notifications">
                        <a ng-href="@{{ baseURL + '/me/notifications/' + notification._id }}">
                            <p class="h4">@{{ notification.short_text }}</p>
                        </a>
                        <hr>
                    </div>
                </div>


            </div>
        </div>
    </div>

</div>


@stop
