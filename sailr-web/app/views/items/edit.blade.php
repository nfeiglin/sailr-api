@extends('layout.simple')

@section('content')

<script>
    var sessionToken = '{{ Session::token() }}';
    var updateURL = '{{ URL::action('ItemsController@update', $item->id) }}'
    var itemModel = {{ $jsonItem }};
    itemModel.price = parseFloat(itemModel.price);

    if(itemModel.ship_price >= 0 || itemModel.ship_price.length > 0) {
        itemModel.ship_price = parseFloat(itemModel.ship_price);
    }

    var filteredItem = itemModel.filter(function(val) {
        return !(val === "" || typeof val == "undefined" || val === null);
    });
</script>

<script>
    function editController($scope, $http) {
        $scope.item = {};
        $scope.item = itemModel;

        /* Set the inital values for these fields as the data binding is a bit dodgey */
        document.getElementById('title-heading').innerText = $scope.item.title;
        document.getElementById('item-description').innerHTML = $scope.item.description;
       // document.getElementById('item-price').value = $scope.item.price;
         document.scope = $scope;

        $scope.buttonPressed = function() {
          console.log($scope.desc);
            console.log($scope);
        };

        $scope.saveChanges = function() {
            $scope.posting = true;

            var data = $scope.item;
            data._token = sessionToken;
            console.log('the data to be sent is ' + JSON.stringify(data));

            $http.put(updateURL, JSON.stringify(data))
                .success(function(data, status, headers, config){
                    $scope.responseData = data;
                    console.log('The response data is: ');
                    console.log($scope.responseData);
                    //window.location = $scope.responseData.redirect_url;
                    $scope.posting = false;
                })

                .error(function(data, status, headers, config) {
                    console.log(data);
                    $scope.posting = false;

                });

        };

        $scope.publish = function() {

        };

    }
</script>
<div class="form-signin panel wide" data-ng-controller="editController">
    <button class="btn btn-block btn-info" ng-click="buttonPressed()"></button>
    <div class="form-signin-heading h2" id="title-heading" data-ng-model="item.title" contenteditable="true"></div>

    <div class="form-group">
        <label for="photos">Images</label>
        <input class="form-control" type="file" multiple="multiple" accept="image/*" name="photos" id="photos" placeholder="Add photos">
    </div>

    {{-- THE MAIN DESCRIPTION  --}}


@{{ item.description }}
    <div class="row form-group">
        <div class="col-sm-6">
            <label for="currency">Currency</label>
            <select class="selectpicker form-control" name="currency" id="currency" data-live-search="true" data-ng-model="item.currency">

                @foreach(Config::get('currencies.both') as $currencyCode => $currencyName)
                <option value="{{ $currencyCode }}" data-subtext="{{ $currencyCode }}">{{ $currencyCode }} ({{ $currencyName }})</option>
                @endforeach

            </select>
        </div>

        <div class="col-sm-6">
            <label for="price">Item price</label>
            <div class="input-group">
                <span class="input-group-addon">@{{ item.currency }}</span>
                <input class="form-control" name="price" id="item-price" placeholder="0.00" type="number" num-binding step="any" ng-model="item.price" min="0" max="9999999">
            </div>

        </div>



    </div>

    <div class="row form-group">
        <div class="col-sm-6">
            <label for="shipping-country">Where will you ship to?</label>
            <select class="form-control" name="ships_to" id="shipping-country" data-ng-model="item.ships_to">
                @foreach(Config::get('countries') as $code => $countryname)
                <option value="{{ $code }}">{{ $countryname }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-sm-6">
            <label for="shipping-price">Shipping price</label>
            <div class="input-group">
                <span class="input-group-addon">@{{ item.currency }}</span>
                <input class="form-control" type="number" num-binding name="shipping-price" placeholder="0.00" id="shipping-price" step="any"  data-ng-model="item.ship_price" min="0" max="999999">
            </div>
        </div>

    </div>

    <div class="row">
        <div class="form-group">
            <div class="col-sm-4">
                <label for="initial_units">Quantity</label>
                <input class="form-control" type="number" num-binding name="initial_units" data-ng-model="item.initial_units" placeholder="0" min="0" max="99999999">
            </div>
        </div>
    </div>

    <div class="row">
    <div class="form-group">
            <h3>Description</h3>

            <div ng-model="item.description" class="well" contenteditable data-edit data-md-ed id="item-description"></div>
        </div>

    </div>

    <div class="row">
        <div class="form-group">
            <div class="btn-group">
                <a data-ng-click="saveChanges()" class="btn btn-lg btn-primary" ng-if="!posting">Save changes</a>
                <div class="heartbeat" ng-if="posting">
                    Loading..
                </div>
            </div>
            <a data-ng-click="saveChanges()" class="btn btn-lg pull-right btn-default">Publish</a>
        </div>
    </div>

</div>


@stop
