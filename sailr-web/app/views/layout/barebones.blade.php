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

    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/custom.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('slick/slick.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('js/card/css/card.css') }}">

    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.16/angular.min.js"></script>
    <script src="https://code.angularjs.org/1.2.18/angular-animate.min.js"></script>
    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
    <script type="text/javascript" src="//code.jquery.com/jquery-1.11.0.min.js"></script>
    <script src="{{ URL::asset('js/angular-file/angular-file-upload.min.js') }}"></script>
    <script src="https://code.angularjs.org/1.2.18/angular-sanitize.min.js"></script>
    <script src="{{ URL::asset('js/directives.js') }}"></script>
    <script src="{{ URL::asset('js/controllers/feed/feedController.js') }}"></script>
    <script>
        var csrfToken = '{{ Session::token() }}';
        var baseURL = '{{ URL::to('/') }}';
    </script>

    <script src="{{ URL::asset('js/card/js/card.js') }}"></script>

    <script>
        var usersName = '{{{ $user->name or ''}}}';
        Stripe.setPublishableKey('{{ Config::get("stripe.sandbox.publishable") }}');
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
    <script src="{{ URL::asset('js/twitter-text-1.9.1.js') }}"></script>
    <script src="{{ URL::asset('js/lib/medium-editor.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/main.js') }}"></script>
</body>

</html>