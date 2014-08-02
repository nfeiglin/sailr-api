@extends('layout.main')
    @section('head')
        <script>var username = '{{ $username or '' }}';</script>
    @stop

    @section('content')
        <div class="col-xs-12" ng-controller="collectionsIndexController">
            <p class="h3 text-muted ng-cloak text-center" data-ng-if="message" data-ng-bind="message"></p>
            <div class="dots" ng-if="loading"></div>

            <div ng-repeat="collection in collections" class="collection-item">
                <h3><a data-ng-href="@{{ baseURL + '/' + username + '/collections/' + collection.id }}">@{{ collection.title }}</a></h3>
            </div>


        </div>
    @stop