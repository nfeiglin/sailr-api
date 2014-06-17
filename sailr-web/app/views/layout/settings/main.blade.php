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

    <script type="text/javascript" src="//code.jquery.com/jquery-1.11.0.min.js"></script>
    <script src="{{ URL::asset('js/angular-file/angular-file-upload-shim.min.js') }}"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.16/angular.min.js"></script>
    <script src="https://code.angularjs.org/1.2.18/angular-animate.min.js"></script>
    <script src="{{ URL::asset('js/angular-file/angular-file-upload.min.js') }}"></script>
    <script src="{{ URL::asset('js/directives.js') }}"></script>
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">

    <link rel="stylesheet" type="text/css" href="{{-- URL::asset('css/bootstrap.css') --}}">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/custom.css') }}">
    {{-- <link rel="stylesheet" href="{{ URL::asset('css/login.css') }}"> --}}
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('slick/slick.css') }}">

    <script src="{{ URL::asset('js/controllers/feed/feedController.js') }}"></script>
    <script>
        var csrfToken = '{{ Session::token() }}';
        var baseURL = '{{ URL::to('/') }}';
    </script>

    @yield('head','')
</head>


<body class="no-purple" ng-controller="feedController">

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
            @if(Session::has('fail'))<div class="alert alert-danger">{{ Session::get('fail') }}</div> @endif

            @yield('content')
        </div>
    </div>
    @yield('full-width-bottom', '')
</div>

<link rel="stylesheet" href="http://css-spinners.com/css/spinners.css" type="text/css">
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/humane-js/3.0.6/themes/original.min.css">
<script src="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
<script type="text/javascript" src="{{ URL::asset('slick/slick.min.js') }}"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/humane-js/3.0.6/humane.min.js"></script>
<script src="{{ URL::asset('js/twitter-text-1.9.1.js') }}"></script>
<script src="{{ URL::asset('js/lib/medium-editor.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/main.js') }}"></script>
@yield('bottom', '')
</body>

</html>