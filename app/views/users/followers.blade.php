@extends('layout.profile.main')
@section('below')

<h2>{{ $page_type or 'Followers/Following' }}</h2>
<div class="row">
    <div class="col-sm-12 col-md-12 col-lg-12">

        @foreach($followers as $follower)
            <div class="item-user panel panel-default col-sm-6 col-md-6 col-lg-6">
                <a href="{{ action('UsersController@show', $follower['username']) }}">
                    <img src="{{ $follower['profile_img'][0]['url'] or '//sailr.co/img/default-sm.jpg' }}" alt="{{{ $follower['name']}}}'s profile image" class="item-user-img img-circle" draggable="false">
                </a>

                <a href="{{ action('UsersController@show', $follower['username']) }}" class="h4 name">
                    {{  $follower['name'] }}
                </a>

                <a href="{{ action('UsersController@show', $follower['username']) }}" class="h5 username text-primary">
                    {{ '@' . $follower['username'] }}
                </a>

                <p sailr-entity-link>{{{ str_limit($follower['bio'], 200) }}}</p>

            </div>
        @endforeach
    </div>
</div>

@stop
