<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
<link href='//fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700|Lato:400,700' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="{{ URL::asset('build/v1/css/base.min.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/humane-js/3.0.6/themes/original.min.css">
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/jquery.slick/1.3.6/slick.css"/>
<script>
    var csrfToken = '{{ Session::token() }}';
    var baseURL = '{{ URL::to('/') }}';
    var loggedInUser = {{ $loggedInUser or 'false' }};
</script>