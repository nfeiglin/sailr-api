@extends('layout.simple')

@section('content')

<div class="form-signin panel wide">
    <h2 class="form-signin-heading">Add an item</h2>

    {{ Form::open(['action' => 'ItemsController@store', 'files' => true])}}
    <div class="form-group">
        <label for="photos">Images</label>
        <input class="form-control" type="file" multiple="multiple" accept="image/*" name="photos" id="photos" placeholder="Add photos">
    </div>

    <input class="form-control form-group" name="title" placeholder="Product title" data-ng-model="title">

    <textarea ng-model="desc" class="form-control form-group" name="description" placeholder="A short or long description of the product, its features and other things to know goes here" rows="10"></textarea>

    <div class="row form-group">
        <div class="col-sm-6">
            <label for="currency">Currency</label>
            <select class="selectpicker form-control" name="currency" id="currency" data-live-search="true" data-ng-model="currency">
                @foreach(Config::get('currencies') as $currencyCode => $currencyName)
                <option value="{{ $currencyCode }}" data-subtext="{{ $currencyCode }}">{{ $currencyCode }} ({{ $currencyName }})</option>
                @endforeach
            </select>
        </div>

        <div class="col-sm-6">
            <label for="price">Item price</label>
            <div class="input-group">
                <span class="input-group-addon">@{{ currency }}</span>
                <input class="form-control" name="price" placeholder="0.00" type="number" data-ng-model="price">
            </div>

        </div>



    </div>

    <div class="row form-group">
        <div class="col-sm-6">
            <label for="shipping-country">Where will you ship to?</label>
            <select class="form-control" name="shipping-country" data-live-search="true" id="shipping-country" data-ng-model="shipCountry">
                @foreach(Config::get('countries') as $code => $countryname)
                <option value="{{ $code }}">{{ $countryname }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-sm-6">
            <label for="shipping-price">Shipping price</label>
            <div class="input-group">
                <span class="input-group-addon">@{{ currency }}</span>
                <input class="form-control" name="shipping-price" placeholder="0.00" id="shipping-price" data-ng-model="shipPrice">
            </div>
        </div>

    </div>

        <div class="row">
            <div class="form-group">
                <div class="col-sm-4">
                    <label for="initial_units">Quantity</label>
                    <input class="form-control" type="number" name="initial_units" data-ng-model="quantity" placeholder="0">
                </div>
           </div>
       </div>
    <div class="form-group row">
        <p class="h4">In summary: You have @{{ quantity }} @{{ title }}'s that you will sell to @{{ shipCountry }} for @{{ currency + shipPrice }}</p>
    </div>

</div>

</form>


@stop
