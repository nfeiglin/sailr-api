<link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="{{ URL::asset('css/custom.css') }}">
<link rel="stylesheet" type="text/css" href="{{ URL::asset('slick/slick.css') }}">
<link rel="stylesheet" type="text/css" href="{{ URL::asset('/js/card/css/card.css') }}">

<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.18/angular.min.js"></script>
<script src="{{ URL::asset('js/angular-file/angular-file-upload-shim.min.js') }}"></script>
<script src="https://code.angularjs.org/1.2.18/angular-animate.min.js"></script>
<script src="{{ URL::asset('js/angular-file/angular-file-upload.min.js') }}"></script>
<script src="https://code.angularjs.org/1.2.18/angular-sanitize.min.js"></script>
<script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
<script src="{{ URL::asset('js/twitter-text-1.9.1.js') }}"></script>
<script src="{{ URL::asset('js/directives.js') }}"></script>
<script src="{{ URL::asset('js/controllers/feed/feedController.js') }}"></script>
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script>
    Stripe.setPublishableKey('{{ Config::get("stripe.sandbox.publishable") }}');
</script>
<script src="{{ URL::asset('js/card/js/card.js') }}"></script>

<script>
    var csrfToken = '{{ Session::token() }}';
    var baseURL = '{{ URL::to('/') }}';
    var loggedInUser = {{ $loggedInUser or 'false' }};
</script>