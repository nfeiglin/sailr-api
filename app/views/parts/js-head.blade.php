<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
<link href='//fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic|Montserrat:400,700' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="{{ URL::asset('build/css/all.min.css') }}">
<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.18/angular.min.js"></script>

<script>
    var csrfToken = '{{ Session::token() }}';
    var baseURL = '{{ URL::to('/') }}';
    var loggedInUser = {{ $loggedInUser or 'false' }};
</script>