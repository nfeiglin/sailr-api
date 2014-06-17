@extends('layout.main')
@section('head')
<link rel="stylesheet" href="{{ URL::asset('css/login.css') }}">
@stop

@section('content')

<form class="form-signin panel" role="form" action="{{URL::action('SessionController@store')}}" method="post" validate="validate">
    {{ Form::token() }}
    <a href="{{url('')}}"><img src="/images/logo-500.png" class="img-responsive"></a>
    @if(Session::get('message'))
    	<p class="form-signin message h5 well well-sm text-danger">{{ Session::get('message') }}</p>
    @else
    <p class="form-signin message h5 well well-sm">Welcome back.</p>
    @endif
    <input type="text" class="form-control" value="{{{ Input::old('username') }}}" placeholder="Email address or Username" name="username" required="required" autofocus>
    <input type="password" class="form-control" placeholder="Password" name="password" required="required">

    <button class="btn btn-lg btn-big btn-turq btn-block" type="submit">Login</button>
    <a class="text-muted" style="margin-top: 15px; margin-bottom: 4px;" href="{{ URL::action('RemindersController@getRemind') }}">Forgot password?</a>
</form>
@stop
