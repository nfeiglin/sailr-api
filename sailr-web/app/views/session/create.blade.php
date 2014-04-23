@extends('layout.main')

@section('content')
<link rel="stylesheet" href="{{ URL::asset('css/login.css') }}">

<form class="form-signin" role="form" action="{{URL::action('UsersController@store')}}" method="post" validate="validate">
    <a href="{{url('')}}"><img src="/images/logo-500.png" class="img-responsive"></a>
    @if(Session::get('message'))
    	<p class="form-signin message h5 well well-sm text-danger">{{ Session::get('message') }}</p>
    @else
    <p class="form-signin message h5 well well-sm">Welcome back.</p>
    @endif
    <input type="text" class="form-control" value="{{{ Input::old('username') }}}" placeholder="Email address or Username" name="username" required="required" autofocus>
    <input type="password" class="form-control" placeholder="Password" name="password" required="required">

    <button class="btn btn-lg btn-primary btn-block" type="submit">Sign up</button>
</form>
@stop
