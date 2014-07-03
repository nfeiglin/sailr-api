<!DOCTYPE html>
<html lang="en" ng-app="app">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Sailr | {{{ Str::limit($title, 140) }}}</title>
    @include('parts.js-head')
    <link rel="stylesheet" href="{{ URL::asset('css/login.css') }}">

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

<link rel="stylesheet" href="http://css-spinners.com/css/spinners.css" type="text/css">
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/humane-js/3.0.6/themes/original.min.css">
<script src="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
<script type="text/javascript" src="{{ URL::asset('slick/slick.min.js') }}"></script>
<link type="text/css" rel="stylesheet" href="{{ URL::asset('css/medium-editor.min.css') }}">
<link type="text/css" rel="stylesheet" href="{{ URL::asset('css/medium-bootstrap.min.css') }}">
<script src="//cdnjs.cloudflare.com/ajax/libs/humane-js/3.0.6/humane.min.js"></script>
<script src="{{ URL::asset('js/lib/medium-editor.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/main.js') }}"></script>

</body>

</html>