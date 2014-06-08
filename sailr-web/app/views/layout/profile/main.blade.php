@extends('layout.simple')
@section('content')

<div class="form-signin wide panel">

    @foreach($user['profile_img'] as $prof_array)
        @if ($prof_array['type'] == 'medium')
            <img src="{{ $prof_array['url'] or '' }}" alt="{{{ $user['name'] or '' }}}'s profile image" class="img-circle img-responsive center-block" draggable="false">
        @endif
    @endforeach

    <a href="{{ URL::action('UsersController@show', $user['username'] ) }}" class="h2"> {{{ $user['name'] }}}</a>
    <a href="{{ URL::action('UsersController@show', $user['username'] ) }}" class="h4">{{{ $user['username'] }}}</a>

    @if(Auth::check() && $is_self)
    <a href="{{ URL::action('SettingsController@getAccount') }}" class="btn btn-md btn-default pull-right">My settings</a>
    @endif
    <div class="row">
        <div class="col-sm-4 center-block">
            <p class="h6">Following</p>
            <p>{{ $no_of_following }}</p>
        </div>

        <div class="col-sm-4 center-block">
            <p class="h6">Following</p>
            <p>{{ $no_of_followers }}</p>
        </div>
    </div>
    @if($you_follow != true && Auth::check() && !$is_self)
    {{ Form::open(['action' => 'RelationshipsController@store' ]) }}
    <input type="hidden" value="{{{ $user['username'] }}}" name="username">
    <button class="btn btn-lg btn-primary btn-block" type="submit"><span class="glyphicon glyphicon-user"></span> Follow</button>
    {{ Form::close() }}

    @endif

    @if($you_follow == true && Auth::check() && !$is_self)
    {{ Form::open(['action' => 'RelationshipsController@destroy', 'method' => 'delete' ]) }}
    <input type="hidden" value="{{{ $user['username'] }}}" name="username">
    <button class="btn btn-lg btn-primary btn-block" type="submit" id="unfollow-btn">Following</button>

    @if($follows_you)
        <span class="label label-info">{{{ $user['username'] }}} follows you</span>
    @endif
    {{ Form::close() }}
    @endif

    @if(!Auth::check())
    <a class="btn btn-lg btn-primary btn-block" href="#loginModal" data-toggle="modal" data-target="#loginModal" title="Please log in to follow">Follow {{{ $user['username'] }}}</a>
    @endif

    @if($user['bio']) <p class="well autolink-text">{{{ $user['bio'] }}}</p> @endif

    @yield('below')

</div>

@if(!Auth::check())
<div class="modal fade" id="loginModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title">Please Login or Register to Follow</h3>
            </div>
            <div class="modal-body">
                @include('parts.not_logged_in')
            </div>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@endif
@stop