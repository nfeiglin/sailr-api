@extends('layout.main')

@section('head')
<script>
    var sailr = {
        notifications: {{ $notifications }}
    };
</script>
<script src="{{ URL::asset('js/controllers/notifications/notificationsController.js') }}"></script>
@stop

@section('content')

<div data-ng-controller="notificationsController">

    <div class="row">
        <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12">
            <div class="panel" ng-repeat="notification in notifications">
                <div class="panel-body">
                    <div class="long" ng-if="long_html in notification">
                        @{{ notification.long_html }}
                    </div>
            </div>


        </div>
    </div>
</div>

</div>


@stop
