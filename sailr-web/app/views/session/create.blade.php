@extends('layout.main')
@section('content')
<link rel="stylesheet" href="{{ URL::asset('css/login.css') }}">

<form class="form-signin" role="form" action="{{URL::action('SessionController@store')}}" method="post">
    <a href="{{url('')}}"><img src="/images/logo-500.png" class="img-responsive"></a>
    <p class="form-signin message h4 well well-sm">Welcome back.</p>
    <input type="text" class="form-control" placeholder="Email address or Username" name="username" required autofocus>
    <input type="password" class="form-control" placeholder="Password" name="password" required>

    <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
</form>
@stop
