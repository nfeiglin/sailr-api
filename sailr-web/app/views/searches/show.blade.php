@extends('layout.main')

@section('head')
<script>
    var sessionToken = '{{ Session::token() }}';
    var sailr = {
        results: {{ $results }}
    };
</script>
<script src="{{ URL::asset('js/controllers/search/searchController.js') }}"></script>
@stop

@section('content')

<div data-ng-controller="searchController">

    <div class="row">
        <p class="lead text-white" ng-hide="results.users.length > 0">Sorry, no users match your search</p>
        <div class="col-md-4 col-lg-3 col-sm-4 col-xs-6" ng-repeat="user in results.users">
            <div class="thumbnail">
                <a ng-href="@{{ baseURL + '/' + user.username }}"><h3>@{{ user.name }}</h3></a>
                <a ng-href="@{{ baseURL + '/' + user.username }}"><h4>@@{{ user.username }}</h4></a>
                    <img class="img-responsive img-circle" ng-src="@{{ user.profile_img[0].url }}">
                    <div class="panel">
                        <p class="panel-body">
                            @{{ user.bio }}
                        </p>
                    </div>
            </div>
        </div>
    </div>

    <div class="row">
        <p class="lead text-white" ng-hide="results.items.length > 1">Sorry, no products match your search</p>
        <div class="col-md-4 col-lg-3 col-sm-4 col-xs-6" ng-repeat="item in results.items">
            <div class="thumbnail">
                <a ng-href="@{{ baseURL + '/item/' + item.id }}"><h3>@{{ item.title }}</h3>
                    <img class="img-responsive" ng-src="@{{ item.photos[0].url }}">
                    <button class="btn btn-md btn-block btn-primary">@{{ item.currency }}@{{ item.price }}</button>
                </a>
            </div>
        </div>
    </div>


</div>


@stop
