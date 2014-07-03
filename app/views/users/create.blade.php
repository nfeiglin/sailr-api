@extends('layout.simple')

@section('content')

<form class="form-signin register panel" role="form" action="{{URL::action('UsersController@store')}}" method="post" validate="validate">
    {{ Form::token() }}
    <a href="{{url('')}}"><img src="/images/logo-500.png" class="img-responsive"></a>
    @if(Session::get('message'))
    	<p class="form-signin message h5 well well-sm text-danger">{{ Session::get('message') }}</p>
    @elseif($errors->first())
    	<p class="form-signin message h5 well well-sm text-danger">{{ $errors->first() }}</p>
    @else
    <p class="form-signin message h5 well well-sm">Welcome, we're glad to have you with us.</p>
    @endif

    <input type="text" class="form-control" value="{{{ Input::old('name') }}}" placeholder="Name" name="name" required="required" autofocus="autofocus">
    <input type="email" class="form-control" value="{{{ Input::old('email') }}}" placeholder="Email" name="email" required="required">
    <input type="text" class="form-control" value="{{{ Input::old('username') }}}" placeholder="Username" name="username" required="required">

    <input type="password" class="form-control" placeholder="Password" name="password" required="required">


        <div class="form-control-static small">
            Agree to our <a href="{{ URL::action('termsOfService') }}">terms of service</a> and <a href="{{ URL::action('privacyPolicy') }}">privacy policy</a>
        </div>
        <input type="checkbox" class="form-control" name="terms_of_service" value="1">
    <input class="btn btn-lg btn-turq btn-block btn-big" type="submit" value="Sign up">
</form>
    </div>



<div class="clearfix" style="margin-bottom: 65px">

</div>
@stop