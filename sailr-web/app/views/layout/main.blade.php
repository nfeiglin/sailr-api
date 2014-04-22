<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.16/angular.js"></script>
   <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.css">
   <link rel="stylesheet" href="{{ URL::asset('css/custom.css') }}">
   <title>Sailr | {{{$title}}}</title>

</head>

<body>
    @if($hasNavbar == 1)
        @include('parts.navbar')
        <div id="backgroundSwag" class="jumbotron">
            <div class="container">
                <h1>{{ $title }}</h1>
            </div>
        </div>
    @endif

    <div class="row">
            <div class="container">
                @if(isset($message))<div class="alert alert-warning">{{ $message}}</div> @endif
                <div class="col-lg-10 col-lg-offset-1 col-md-10 col-md-offset-1 col-sm-12 col-xs-12">

                    @yield('content')
                </div>
            </div>

    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.js"></script>
    <script src="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.js"></script>
</body>

</html>