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
                            <h3 class="text-center" ng-bind="user.name"></h3>
                            <h4 class="text-center text-muted">@<span ng-bind="user.username">{{ $user->username }}</span></h4>
                        </div>
                        <a href="#" class="btn btn-md btn-default ng-cloak" onclick="openFileBrowser()" id="fileButton" ng-bind="fileButtonText">Select new...</a>

                        <form name="photos" ng-submit="photos.$valid" method="post" action="{{ URL::action('ProfileImageController@store') }}" id="imageForm" enctype="multipart/form-data">
                            <input type="file" accept="image/*" id="addFiles" name="photos" ng-required="required" required="required" class="form-control">
                            {{ Form::token() }}
                            <div ng-if="showSubmit">
                                <button type="submit" class="btn btn-block btn-blue ng-cloak" ng-disabled="photos.$invalid">Submit</button>
                                <p class="help-block ng-cloak">Press submit to update your profile photo</p>
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
            {{ Form::text('username',null, ['class' => 'form-control', 'id' => 'username', 'ng-model' => 'user.username', 'autocomplete' => 'off']) }}
            <p><span ng-bind="baseURL"></span>/<b ng-bind="user.username"></b></p>
            <p class="help-block">Changing your username will change your store URL and may affect links to your store.</p>
        </div>

        <div class="form-group">
            {{ Form::label('Email') }}
            {{ Form::email('email',null, ['class' => 'form-control']) }}
            <p class="help-block">{{ Lang::get('form.paypal-email') }}</p>

        </div>


        <div class="form-group">
            {{ Form::label('Bio') }}
            {{ Form::textarea('bio',null, ['class' => 'form-control', 'rows' => 3]) }}
        </div>



        <button class="btn btn-lg btn-blue btn-block" value="submit" type="submit">Save</button>
        {{ Form::close() }}
    </div>

@stop