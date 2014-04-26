<!DOCTYPE html>
<html lang="en">
<head>
   <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.css">

    <link rel="stylesheet" type="text/css" href="{{-- URL::asset('css/bootstrap.css') --}}">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/custom.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('slick/slick.css') }}">

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
                <div class="col-lg-10 col-lg-offset-1 col-md-10 col-md-offset-1 col-sm-12 col-xs-12">
                    @if(isset($message))<div class="alert alert-warning">{{ $message }}</div> @endif
                    @if(isset($success))<div class="alert alert-success">{{ $success }}</div> @endif
                
                    @yield('content')
                </div>
            </div>

    </div>


    <script type="text/javascript" src="//code.jquery.com/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
    <script src="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.js"></script>
    <script type="text/javascript" src="{{ URL::asset('slick/slick.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/main.js') }}"></script>
</body>

</html>