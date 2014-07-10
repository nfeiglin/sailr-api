@extends('layout.main')

@section('head')
<script>
    var currencyCodes = {{ json_encode(Config::get('currencies.codes')) }};
</script>

@stop

@section('content')

<div class="row" ng-controller="indexController">
    <div class="cont">
        <div class="panel">

            <div class="panel-heading noSelect" data-ng-click="toggleAdd()">
                <h4 class="text-center">Add item</h4>
            </div>

            <div class="add-new-form panel animate-down vis-hidden" id="addItem">
                <form class='form-horizontal' data-ng-submit="formSubmit()" name="itemForm">
                <input type="hidden" value="{{ Session::token() }}" name="_token">
                <div class="product-list panel-body">
                    <p class="col-xs-12 col-lg-12 col-md-12 col-sm-12 text-warning h6">Ensure that your Sailr account email is the same as your PayPal account email address to get paid when you sell.</p>
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
                            <input class="form-control" name="price" placeholder="0.00" type="number" data-ng-model="price" step="any" min="0" max="999999" required="required">
                        </div>
                    </div>

                    <div class="col-xs-2 col-sm-2 col-lg-1 col-md-1">
                        <input type="submit" class="btn btn-primary btn-block" value="Add" ng-disabled="itemForm.$invalid || posting" ng-if="!posting">

                        <div ng-if="posting">
                            <div class="dots btn-block">
                                Loading...
                            </div>
                        </div>
                    </div>


                </div>
                </form>
            </div>
        </div>




<div class="row">
    <div class="product-list panel">

        <div class="product-index-item panel-body" style="padding: 0">
        </div>
        <hr>

        @foreach($items as $item)
        <div class="product-index-item panel-body" style="padding: 0">
            <div class="col-xs-9 col-lg-9 col-md-9 col-sm-9">
                <a class="h4 text-primary" href="{{ URL::action('ItemsController@edit', $item['id']) }}">{{{ $item['title'] }}}</a>
                @if($item['public'] == 1)
                <div class="badge pull-right" style="background: dodgerblue">Published</div>
                @else
                <div class="badge pull-right">Unpublished</div>
                @endif
            </div>
            <div class="col-xs-3 col-lg-3 col-md-3 col-sm-3">
                {{ $item['currency'] }} {{ $item['price'] }}
            </div>

        </div>
        <hr>
        @endforeach
    </div>
</div>



    </div>
</div>

@stop
