@extends('layout.main')
@section('head')
<script>var username = '{{ $username or '' }}';</script>
@stop

@section('content')
<div class="col-xs-12" ng-controller="collectionsIndexController" id="masonryContainer">
    <p class="h3 text-muted ng-cloak text-center" data-ng-if="message" data-ng-bind="message"></p>
    <div class="dots" ng-if="loading"></div>

    <div ng-repeat="item in items" class="collection-item">
        <h3><a data-ng-href="@{{ baseURL + '/' + username + '/product/' + item.id }}">@{{ item.title }}</a></h3>
    </div>


</div>
@stop