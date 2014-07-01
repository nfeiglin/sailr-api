@extends('layout.settings.main')

@section('head')
<script>
    var profileImageURL = '{{ $profileImageURL or 'PROFILE IMAGE URL' }}';
</script>
@stop


@section('content')
    <div class="" ng-controller="updateController">
        {{ Form::model($user, ['action' => 'SettingsController@putAccount', 'method' => 'PUT', 'class' => '', 'validate' => 'validate' ]) }}
        <h2>Account settings</h2>
        <div class="row">
            <div class="col-xs-12 col-lg-12 col-md-12 col-sm-12">
                <div class="col-lg-6 col-lg-offset-3 col-sm-6 col-sm-offset-3 col-xs-12 col-md-6 col-md-offset-3">
                    <div class="thumbnail">
                        <img ng-src="@{{ profileURL }}" src="{{ $profileImageURL }}" class="img-circle img-responsive img-thumbnail">
                        <div class="caption">
                            <h4>@{{ user.name }}</h4>
                        </div>
                        <a href="#" class="btn btn-lg btn-primary btn-block" onclick="openFileBrowser()">
                            <span class="glyphicon glyphicon-cloud-upload"></span> Add photo
                            <input type="file" ng-file-select="onFileSelect($files)" accept="image/*" id="addFiles">
                        </a>
                    </div>
                </div>
            </div>
        </div>






        <div class="form-group">
            {{ Form::label('Name') }}
            {{ Form::text('name',null, ['class' => 'form-control', 'ng-model' => 'user.name']) }}
        </div>

        <div class="form-group">
            {{ Form::label('Username') }}
            {{ Form::text('username',null, ['class' => 'form-control']) }}
        </div>

        <div class="form-group">
            {{ Form::label('Email') }}
            <p class="alert alert-danger" style="color: #000000">{{ Lang::get('form.paypal-email') }}</p>
            {{ Form::email('email',null, ['class' => 'form-control']) }}

        </div>


        <div class="form-group">
            {{ Form::label('Bio') }}
            {{ Form::textarea('bio',null, ['class' => 'form-control', 'rows' => 3]) }}
        </div>



        <button class="btn btn-lg btn-primary btn-block" value="submit" type="submit">Save</button>
        {{ Form::close() }}
    </div>

@stop