<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
<link href='//fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic|Montserrat:400,700' rel='stylesheet' type='text/css'>

{{--
@if(App::environment('local', 'debug', 'testing'))
{{ \Sailr\TestPipe\TestPipe::make()->tags('css') }}
@else
--}}
<link rel="stylesheet" href="{{ URL::asset('build/css/base.min.css') }}">
{{-- @endif --}}

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/humane-js/3.0.6/themes/original.min.css">

<script>
    var csrfToken = '{{ Session::token() }}';
    var baseURL = '{{ URL::to('/') }}';
    var loggedInUser = {{ $loggedInUser or 'false' }};
</script>