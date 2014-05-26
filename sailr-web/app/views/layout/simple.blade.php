<!DOCTYPE html>
<html lang="en" ng-app="app">
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.16/angular.min.js"></script>
    <script src="{{ URL::asset('js/directives.js') }}"></script>
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">

    <link rel="stylesheet" type="text/css" href="{{-- URL::asset('css/bootstrap.css') --}}">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/custom.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/login.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('slick/slick.css') }}">

    <title>Sailr | {{{$title}}}</title>

</head>

<body>
@if($hasNavbar == 1)

    @include('parts.navbar')

@endif

<div class="row">
    <div class="container">
        <div class="col-lg-10 col-lg-offset-1 col-md-10 col-md-offset-1 col-sm-12 col-xs-12" style="margin-top: 30px">
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



<script type="text/javascript" src="//code.jquery.com/jquery-1.11.0.min.js"></script>
<script src="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
<script type="text/javascript" src="{{ URL::asset('slick/slick.min.js') }}"></script>

<script src="{{ URL::asset('js/angular-medium-editor.min.js') }}" type="text/javascript"></script>
<link type="text/css" rel="stylesheet" href="{{ URL::asset('css/medium-editor.min.css') }}">
<link type="text/css" rel="stylesheet" href="{{ URL::asset('css/medium-bootstrap.min.css') }}">

{{ HTML::script('js/angular-file/angular-file-upload-shim.min.js') }}
{{ HTML::script('js/angular-file/angular-file-upload.min.js') }}

<script type="text/javascript" src="{{ URL::asset('js/main.js') }}"></script>
<link rel="stylesheet" href="http://css-spinners.com/css/spinners.css" type="text/css">

</body>

</html>