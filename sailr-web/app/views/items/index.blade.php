@extends('layout.main')
@section('content')

<div class="row" data-ng-controller="indexController">
    <script>
        function indexController($scope, $http, $q, $location) {
            $scope.currency = 'USD';
            $scope.codes = {{ json_encode(Config::get('currencies.codes')) }};
            $scope.handleCodeChange = function($index) {
                $scope.currency = $scope.codes[$index];
                console.log($scope.currency);
            };

            $scope.toggleAdd = function() {
                $scope.shouldShowAdd = !$scope.shouldShowAdd;
                $('#addItem').slideToggle(300);
                console.log('Show pressed');
            };

        document.scope = $scope;

        $scope.formSubmit = function() {
            $scope.posting = true;
            $scope.formData = {_token: $('#csrf-token').val(), title: $scope.title, currency: $scope.currency, price: $scope.price};
            console.log($scope.formData);

            $http.post('/items', JSON.stringify($scope.formData))
                .success(function(data, status, headers, config){
                    console.log('tthe data is ' + JSON.stringify(data));
                    $scope.responseData = data;
                    console.log($scope.responseData);
                    window.location = $scope.responseData.redirect_url;
                    $scope.posting = false;
                })

            .error(function(data, status, headers, config) {
                    console.log(data);
                    $scope.posting = false;

            });
        };

        }
    </script>
    <div class="cont">
        <div class="panel">

            <div class="panel-heading noSelect" data-ng-click="toggleAdd()">
                <h4 class="text-center">Add item</h4>
            </div>

            <div class="add-new-form panel animate-down vis-hidden" id="addItem">
                <form class='form-horizontal' data-ng-submit="formSubmit()" name="itemForm">
                <input type="hidden" value="{{ Session::token() }}" ng-init="{{ Session::token() }}" data-ng-model="csrftoken" id="csrf-token">
                <div class="product-list panel-body">
                    <div class="col-xs-5 col-lg-8 col-md-8 col-sm-7">
                        <input type="text" class="form-control" placeholder="Item name" name="title" data-ng-model="title" ng-maxlength="255" required="required">
                    </div>

                    <div class="col-xs-5 col-lg-3 col-md-3 col-sm-3">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <div class="dropdown">
                                    <div class="dropdown-toggle noSelect cursor-pointer" data-toggle="dropdown">
                                        <span ng-if="currency">@{{ currency }}</span>
                                    </div>
                                    <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                                        <li data-ng-click="handleCodeChange($index)" data-ng-repeat="code in codes"><a href="#">@{{ code }}</a></li>
                                    </ul>
                                </div>
                            </div>
                            <input type="hidden" ng-value="currency" name="currency" id="cur-hid">
                            <input class="form-control" name="price" placeholder="0.00" type="number" data-ng-model="price" min="0" max="999999" required="required">
                        </div>
                    </div>

                    <div class="col-xs-2 col-sm-2 col-lg-1 col-md-1">
                        <input type="submit" class="btn btn-primary btn-block" value="Add" ng-disabled="itemForm.$invalid || posting" ng-if="!posting">

                    <div class="heartbeat btn-block" ng-if="posting">
                        Loading..
                    </div>
                    </div>


                </div>
                </form>
            </div>
        </div>




        @foreach($items as $item)
        <div class="product-list panel panel-body">
            <div class="col-xs-9 col-lg-9 col-md-9 col-sm-9">
                {{{ $item['title'] }}}
            </div>

            <div class="col-xs-3 col-lg-3 col-md-3 col-sm-3">
                {{ $item['currency'] }} {{ $item['price'] }}
            </div>
        </div>
        <div class="divider"></div>
        @endforeach
    </div>
</div>


<link rel="stylesheet" href="http://css-spinners.com/css/spinners.css" type="text/css">
@stop
