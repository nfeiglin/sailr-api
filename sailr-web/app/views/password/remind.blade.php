@extends('layout.simple')

@section('content')
<form class="form-signin panel" role="form" action="{{URL::action('RemindersController@postRemind')}}" method="post" validate="validate">
    {{ Form::token() }}
    <a href="{{url('')}}"><img src="/images/logo-500.png" class="img-responsive"></a>
    @if(Session::get('message'))
    	<p class="form-signin message h5 well well-sm text-danger">{{ Session::get('message') }}</p>
    @else
    <p class="form-signin message h5 well well-sm">It's okay, we all forget sometimes.</p>
    @endif
    <input type="email" class="form-control" value="{{{ Input::old('email') }}}" placeholder="Email address" name="email" required="required" autofocus>
    <button class="btn btn-lg btn-primary btn-block" type="submit">Send reset email</button>
</form>
@stop
