@extends('layout.simple')
@section('content')
    {{ Form::model($user, ['action' => 'SettingsController@putAccount', 'method' => 'PUT', 'class' => 'form-signin panel', 'validate' => 'validate' ]) }}
        <a href="{{url('')}}"><img src="/images/logo-500.png" class="img-responsive"></a>
        <h2>Account settings</h2>

        <div class="form-group">
            {{ Form::label('Name') }}
            {{ Form::text('name',null, ['class' => 'form-control']) }}
        </div>

        <div class="form-group">
            {{ Form::label('Email') }}
            <p class="alert alert-danger" style="color: #000000">{{ Lang::get('form.paypal-email') }}</p>
            {{ Form::email('email',null, ['class' => 'form-control']) }}

        </div>

        <div class="form-group">
            {{ Form::label('Username') }}
            {{ Form::text('username',null, ['class' => 'form-control']) }}
        </div>

        <div class="form-group">
            {{ Form::label('Bio') }}
            {{ Form::textarea('bio',null, ['class' => 'form-control', 'rows' => 3]) }}
        </div>



        <button class="btn btn-lg btn-primary btn-block" value="submit" type="submit">Update</button>
    {{ Form::close() }}
@stop