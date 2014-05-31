@extends('layout.simple')

@section('head')
<script>
    var sessionToken = '{{ Session::token() }}';
    var updateURL = '{{ URL::action('ItemsController@update', $item->id) }}'
    var countries = {{ json_encode(Config::get('countries')) }};
    var itemModel = {{ $jsonItem }};
</script>
<script src="{{ URL::asset('js/controllers/items/editController.js') }}"></script>
@stop

@section('content')

<div class="form-signin panel wide" data-ng-controller="editController">
    <button class="btn btn-block btn-info" ng-click="buttonPressed()"></button>
    <div class="form-signin-heading h2" id="title-heading" data-ng-model="item.title" contenteditable="true"></div>

    <div class="row file-drop ease-in-out" ng-file-drop="onFileSelect($files)" ng-file-drag-over-class="dragover">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

            <div class="col-sm-4 col-lg-4 col-sm-4 col-md-4" data-ng-repeat="photo in photos">
                <div class="thumbnail">
                    <img ng-src="@{{ photo.url }}" class="img-responsive" alt="Thumbnail preview image" draggable="false">
                </div>
            </div>

            <div class="col-xs-12 col-lg-12">
                <a href="#" class="btn btn-lg btn-primary btn-block" onclick="openFileBrowser()">
                    <span class="glyphicon glyphicon-cloud-upload"></span> Add photo
                    <input type="file" ng-file-select="onFileSelect($files)" accept="image/*" id="addFiles">
                </a>
                <p class="help-block">Photos will be automagically cropped and scaled to a square. Maximum size 8MB.</p>
            </div>

        </div>

    </div>



    {{-- THE MAIN DESCRIPTION --}}


    {{-- item.description --}}
    <div class="row form-group">
        <div class="col-sm-6">
            <label for="currency">Currency</label>
            <select class="selectpicker form-control" name="currency" id="currency" data-live-search="true" data-ng-model="item.currency">

                @foreach(Config::get('currencies.both') as $currencyCode => $currencyName)
                <option value="{{ $currencyCode }}" data-subtext="{{ $currencyCode }}">
                    {{ $currencyCode }} ({{ $currencyName }})
                </option>
                @endforeach

            </select>
        </div>

        <div class="col-sm-6">
            <label for="price">Item price</label>

            <div class="input-group">
                <span class="input-group-addon ease-in-out">@{{ item.currency }}</span>
                <input class="form-control" name="price" id="item-price" placeholder="0.00" type="number" num-binding step="any" ng-model="item.price" min="0" max="9999999">
            </div>

        </div>


    </div>

    <div class="row form-group">
        <div class="col-sm-6">
            <label for="shipping-country">Where will you ship to?</label>
            <select class="form-control" name="ships_to" id="shipping-country" data-ng-model="item.ships_to">
                <option data-ng-repeat="(code, name) in countries" value="@{{ code }}">@{{ name }}</option>
            </select>
        </div>

        <div class="col-sm-6">
            <label for="shipping-price">Shipping price</label>

            <div class="input-group">
                <span class="input-group-addon ease-in-out">@{{ item.currency }}</span>
                <input class="form-control" type="number" num-binding name="shipping-price" placeholder="0.00"
                       id="shipping-price" step="any" data-ng-model="item.ship_price" min="0" max="999999">
            </div>
        </div>

    </div>

    <div class="row">
        <div class="form-group">
            <div class="col-sm-4">
                <label for="initial_units">Quantity</label>
                <input class="form-control" type="number" num-binding name="initial_units"
                       data-ng-model="item.initial_units" placeholder="0" min="0" max="99999999">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group">
            <h3>Description</h3>

            <div ng-model="item.description" class="well" contenteditable data-edit data-md-ed
                 id="item-description"></div>
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
            <a data-ng-click="saveChanges()" class="btn btn-lg pull-right btn-default"><i class="glyphicon glyphicon-upload"></i> Publish</a>
        </div>
    </div>

</div>


@stop
