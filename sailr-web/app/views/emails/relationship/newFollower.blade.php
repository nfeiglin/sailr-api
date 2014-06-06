@extends('emails.layout.main')
@section('content')

<p>Hi {{ $following['name'] }}, you have a new follower on Sailr.</p>

<a href="{{ URL::action('UsersController@show', $follower['username']) }}">
    <table class="button twitter">
        <tr>
            <td>
                <h2>{{{ $follower['name'] }}} <small>{{{ '@' . $follower['username'] }}}</small> </h2>
            </td>
        </tr>
    </table>

</a>

<p>Cheers,</p>
<p>Sailr</p>

@stop