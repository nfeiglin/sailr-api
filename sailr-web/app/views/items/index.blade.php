@extends('layout.main')
@section('content')

<div class="row" data-ng-controller="indexController">
    <script>
        function indexController($scope) {
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
        }
    </script>
    <div class="cont">
        <div class="panel">

            <div class="panel-heading noSelect" data-ng-click="toggleAdd()">
                <h4 class="text-center">Add item</h4>
            </div>

            <div class="add-new-form panel animate-down vis-hidden" id="addItem">
                {{ Form::open(['method' => 'post', 'class' => 'form-horizontal', 'action' => 'ItemsController@store' ]) }}
                <div class="product-list panel-body">
                    <div class="col-xs-5 col-lg-8 col-md-8 col-sm-7">
                        <input type="text" class="form-control" placeholder="Item name" name="title">
                    </div>

                    <div class="col-xs-5 col-lg-3 col-md-3 col-sm-3">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <div class="dropdown">
                                    <div class="dropdown-toggle noSelect cursor-pointer" data-toggle="dropdown">
                                        <span data-ng-if="!currency">USD</span>
                                        <span ng-if="currency">@{{ currency }}</span>
                                    </div>
                                    <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                                        <li data-ng-click="handleCodeChange($index)" data-ng-repeat="code in codes"><a href="#">@{{ code }}</a></li>
                                    </ul>
                                </div>
                            </div>
                            <input type="hidden" value="@{{ currency }}" name="currency" id="cur-hid">@{{ currency }}
                            <input class="form-control" name="price" placeholder="0.00" type="number" data-ng-model="price">
                        </div>
                    </div>

                    <div class="col-xs-2 col-sm-2 col-lg-1 col-md-1">
                        <input type="submit" class="btn btn-primary btn-block" value="Add">
                    </div>


                </div>
                {{ Form::close() }}
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



@stop
