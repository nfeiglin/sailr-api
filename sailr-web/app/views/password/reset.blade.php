@extends('layout.simple')

@section('content')
<form class="form-signin panel" role="form" action="{{URL::action('RemindersController@postRemind')}}" method="post" validate="validate">
    {{ Form::token() }}
    <input type="hidden" name="token" value="{{{ $token }}}">
    <a href="{{url('')}}"><img src="/images/logo-500.png" class="img-responsive"></a>
    @if(Session::get('message'))
    	<p class="form-signin message h5 well well-sm text-danger">{{ Session::get('message') }}</p>
    @else
    <p class="form-signin message h5 well well-sm">Choose a new password</p>
    @endif
    <input type="email" class="form-control" value="{{{ Input::old('email') }}}" placeholder="Email address" name="email" required="required" autofocus>
    <input type="password" class="form-control" value="{{{ Input::old('password') }}}" placeholder="New password" name="password" required="required">
    <input type="password" class="form-control" value="{{{ Input::old('password_confirmation') }}}" placeholder="Confirm password" name="password_confirmation" required="required">

    <button class="btn btn-lg btn-primary btn-block" type="submit">Reset password</button>
</form>
@stop
