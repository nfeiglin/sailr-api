@extends('layout.simple')

@section('content')

<script>
    var itemModel = {{ $jsonItem }};
    var filteredItem = arr.filter(function(val) {
        return !(val === "" || typeof val == "undefined" || val === null);
    });
</script>

<script>
    function editController($scope, $http) {
        $scope.item = {};
        $scope.item = itemModel;

         document.scope = $scope;

        $scope.buttonPressed = function() {
          console.log($scope.desc);
            console.log($scope);
        };

    }
</script>
<div class="form-signin panel wide" data-ng-controller="editController">
    <button class="btn btn-block btn-info" ng-click="buttonPressed()"></button>
    <div class="form-signin-heading h2" data-ng-model="item.title" contenteditable="true">@{{ item.title }}</div>
    <input type="text" class="form-control">

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
                <input class="form-control" name="price" placeholder="0.00" type="number" data-ng-model="price" min="0" max="9999999">
            </div>

        </div>



    </div>

    <div class="row form-group">
        <div class="col-sm-6">
            <label for="shipping-country">Where will you ship to?</label>
            <select class="form-control" name="shipping-country" id="shipping-country" data-ng-model="item.ships_to">
                @foreach(Config::get('countries') as $code => $countryname)
                <option value="{{ $code }}">{{ $countryname }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-sm-6">
            <label for="shipping-price">Shipping price</label>
            <div class="input-group">
                <span class="input-group-addon">@{{ currency }}</span>
                <input class="form-control" type="number" name="shipping-price" placeholder="0.00" id="shipping-price" data-ng-model="item.shipPrice" min="0" max="999999">
            </div>
        </div>

    </div>

    <div class="row">
        <div class="form-group">
            <div class="col-sm-4">
                <label for="initial_units">Quantity</label>
                <input class="form-control" type="number" name="initial_units" data-ng-model="item.initial_units" placeholder="0" min="0" max="99999999">
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <h3>Description</h3>

            <div ng-model="item.description" class="well" name="tttt" contenteditable data-edit data-md-ed data-placeholder="NULL"></div>
        </div>

    </div>

</div>


@stop
