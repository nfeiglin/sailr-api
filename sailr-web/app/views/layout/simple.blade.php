<!DOCTYPE html>
<html lang="en" ng-app="app">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Sailr | {{{ Str::limit($title, 140) }}}</title>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.18/angular.min.js"></script>
    <script src="https://code.angularjs.org/1.2.18/angular-animate.min.js"></script>
    <script src="https://code.angularjs.org/1.2.18/angular-sanitize.min.js"></script>
    <script src="{{ URL::asset('js/directives.js') }}"></script>
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="{{-- URL::asset('css/bootstrap.css') --}}">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/custom.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/login.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('slick/slick.css') }}">
    <script src="{{ URL::asset('js/controllers/feed/feedController.js') }}"></script>

    <script>
        var csrfToken = '{{ Session::token() }}';
        var baseURL = '{{ URL::to('/') }}';
        var loggedInUser = {{ $loggedInUser or 'false' }};
    </script>
    @yield('head', '')
</head>

@if(isset($purpleBG) && $purpleBG == true)
<body ng-controller="feedController" class="purpleBackground">
@else
<body ng-controller="feedController">
@endif

@if($hasNavbar == 1)

    @include('parts.navbar')

@endif

<div class="row">
    <div class="container" style="margin-top: 30px">

        <div class="col-lg-8 col-md-8 col-lg-offset-2 col-md-offset-2 col-sm-12 col-xs-12">
            @if(Session::has('message'))<div class="alert alert-warning">{{ Session::get('message') }}</div> @endif
            @if(Session::has('success'))<div class="alert alert-success">{{ Session::get('success') }}</div> @endif
            @if(Session::has('error'))<div class="alert alert-warning">{{ Session::get('error') }}</div> @endif
            @if(Session::has('fail'))<div class="alert alert-danger">{{ Session::get('fail') }}</div> @endif

            @if($errors->first())
                <div class="alert alert-warning">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
            @yield('content')
        </div>
    </div>
</div>

@include('parts.footer')

<script type="text/javascript" src="//code.jquery.com/jquery-1.11.0.min.js"></script>
<script src="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
<script type="text/javascript" src="{{ URL::asset('slick/slick.min.js') }}"></script>

<script src="{{ URL::asset('js/angular-medium-editor.min.js') }}" type="text/javascript"></script>
<link type="text/css" rel="stylesheet" href="{{ URL::asset('css/medium-editor.min.css') }}">
<link type="text/css" rel="stylesheet" href="{{ URL::asset('css/medium-bootstrap.min.css') }}">

{{ HTML::script('js/angular-file/angular-file-upload-shim.min.js') }}
{{ HTML::script('js/angular-file/angular-file-upload.min.js') }}


<link rel="stylesheet" href="http://css-spinners.com/css/spinners.css" type="text/css">
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/humane-js/3.0.6/themes/original.min.css">
<link type="text/css" rel="style" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-jcrop/0.9.12/css/jquery.Jcrop.min.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/humane-js/3.0.6/humane.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-jcrop/0.9.12/js/jquery.Jcrop.min.js"></script>
<script src="{{ URL::asset('js/twitter-text-1.9.1.js') }}"></script>
<script src="{{ URL::asset('js/lib/medium-editor.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/main.js') }}"></script>

</body>

</html>