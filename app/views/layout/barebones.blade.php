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
    @include('parts.js-bottom')
</body>

</html>