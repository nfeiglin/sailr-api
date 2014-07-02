@extends('layout.settings.main')

@section('head')
<script>
    var profileImageURL = '{{ $profileImageURL or 'PROFILE IMAGE URL' }}';
</script>
@stop


@section('content')
    <div class="" ng-controller="updateController">
        <h2>Account settings</h2>
        <div class="row">
            <div class="col-xs-12 col-lg-12 col-md-12 col-sm-12">
                <div class="col-lg-6 col-lg-offset-3 col-sm-6 col-sm-offset-3 col-xs-12 col-md-6 col-md-offset-3">
                    <div class="thumbnail">
                        <img ng-src="@{{ profileURL }}" src="{{ $profileImageURL }}" class="img-circle img-responsive img-thumbnail">
                        <div class="caption">
                            <h3 class="text-center">@{{ user.name }}</h3>
                        </div>
                        <a href="#" class="btn btn-md btn-default" onclick="openFileBrowser()" id="fileButton">
                            @{{ fileButtonText }}
                        </a>

                        <form name="photos" ng-submit="photos.$valid" method="post" action="{{ URL::action('ProfileImageController@store') }}" id="imageForm" enctype="multipart/form-data">
                            <input type="file" accept="image/*" id="addFiles" name="photos" ng-required="required" required="required" class="form-control">
                            {{ Form::token() }}
                            <div ng-if="showSubmit">
                                <button type="submit" class="btn btn-block btn-turq" ng-disabled="photos.$invalid">Submit</button>
                                <p class="help-block">Press submit to update your profile photo</p>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>





        {{ Form::model($user, ['action' => 'SettingsController@putAccount', 'method' => 'PUT', 'class' => '', 'validate' => 'validate' ]) }}
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