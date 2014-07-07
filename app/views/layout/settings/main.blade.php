<!DOCTYPE html>
<html lang="en" ng-app="app">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @if(isset($title))
    <title>Sailr | {{{ Str::limit($title, 140) }}}</title>
    @else
    <title>Sailr</title>
    @endif

    @include('parts.js-head')

    <link rel="stylesheet" href="{{ URL::asset('css/login.css') }}">
    <script src="{{ URL::asset('js/controllers/users/updateController.js') }}"></script>

    @yield('head','')
</head>


<body class="no-purple" ng-controller="feedController">

@include('parts.middle')

@if($hasNavbar == 1)
@include('parts.navbar')
@if(isset($title))
<div id="backgroundSwag" class="jumbotron">
    <div class="container">
        <h1>{{ $title }}</h1>
    </div>
</div>
@endif
@endif

<div class="row">
    @yield('full-width-top', '')
    <div class="container">
        <div class="col-lg-2 col-md-2 col-sm-4 col-xs-12">
            @yield('sidebar', '')
            <div class="sidebar-content panel">
                <div class="btn-group-verticalf">
                    <a href="{{ URL::action('SettingsController@getAccount') }}" class="btn btn-block btn-primary">Account settings</a>
                    <a href="{{ URL::action('BillingsController@index') }}" class="btn btn-block btn-primary">Billing settings</a>
                    <a href="{{ URL::action('SubscriptionsController@index') }}" class="btn btn-block btn-primary">Subscription settings</a>
                </div>
            </div>
        </div>

        <div class="col-lg-8 col-md-8 col-sm-9 col-xs-12">
            @if(Session::has('message'))<div class="alert alert-warning">{{ Session::get('message') }}</div> @endif
            @if(Session::has('success'))<div class="alert alert-success">{{ Session::get('success') }}</div> @endif
            @if(Session::has('error'))<div class="alert alert-warning">{{ Session::get('error') }}</div> @endif
            @if($errors->first())<div class="alert alert-warning">@foreach($errors->all('<li>:message</li>') as $error){{ $error }}@endforeach</div>@endif
            @if(Session::has('fail'))<div class="alert alert-danger">{{ Session::get('fail') }}</div> @endif

            @yield('content')
        </div>
    </div>
    @yield('full-width-bottom', '')
</div>
@yield('bottom', '')
@include('parts.footer')

@include('parts.js-bottom')
</body>

</html>