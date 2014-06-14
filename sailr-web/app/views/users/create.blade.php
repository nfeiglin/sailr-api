@extends('layout.simple')

@section('content')

<form class="form-signin register panel wide" role="form" action="{{URL::action('UsersController@store')}}" method="post" validate="validate">
    {{ Form::token() }}
    <a href="{{url('')}}"><img src="/images/logo-500.png" class="img-responsive"></a>
    @if(Session::get('message'))
    	<p class="form-signin message h5 well well-sm text-danger">{{ Session::get('message') }}</p>
    @elseif($errors->first())
    	<p class="form-signin message h5 well well-sm text-danger">{{ $errors->first() }}</p>
    @else
    <p class="form-signin message h5 well well-sm">Welcome, we're glad to have you with us.</p>
    @endif

    <input type="text" class="form-control" value="{{{ Input::old('name') }}}" placeholder="Name" name="name" required="required" autofocus>
    <input type="email" class="form-control" value="{{{ Input::old('email') }}}" placeholder="Email" name="email" required="required">
    <input type="text" class="form-control" value="{{{ Input::old('username') }}}" placeholder="Username" name="username" required="required">
    <input type="password" class="form-control" placeholder="Password" name="password" required="required">

    <div class="plan-chooser">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="paid col-lg-8 col-md-8 col-sm-6 col-xs-12">
                <div class="square-btn">
                    <div class="square-btn-content">
                        <span class="glyphicon glyphicon-tower big-font text-center"></span>
                        <div class="h3">Awesome</div>
                        <ul>
                            <li>A point</li>
                            <li>Goes here!</li>
                        </ul>
                    </div>

                </div>
            </div>

            <div class="free col-lg-4 col-md-4 col-sm-6 col-xs-12">
                <div class="square-btn">

                </div>
            </div>

        </div>
    </div>

    <button class="btn btn-lg btn-turq btn-block btn-big" type="submit">Sign up</button>
</form>
@stop
