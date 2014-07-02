<!DOCTYPE html>
<html lang="en" ng-app="app">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @if(isset($title))
    <title>{{{ $title }}}</title>
    @else
    <title>Sailr</title>
    @endif

    @include('parts.js-head')

    <script>
        var usersName = '{{{ $user->name or ''}}}';
    </script>

    @yield('head','')
</head>


<body ng-controller="feedController" class="no-bottom-margin">
    @yield('body', '')
    <link rel="stylesheet" href="http://css-spinners.com/css/spinners.css" type="text/css">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/humane-js/3.0.6/themes/original.min.css">
    <script src="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="{{ URL::asset('slick/slick.min.js') }}"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/humane-js/3.0.6/humane.min.js"></script>
    <script src="{{ URL::asset('js/lib/medium-editor.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/main.js') }}"></script>
</body>

</html>