@extends('layout.simple')

@section('content')

<script>
    var sessionToken = '{{ Session::token() }}';
    var updateURL = '{{ URL::action('ItemsController@update', $item->id) }}'
    var countries = {{ json_encode(Config::get('countries')) }};
    var itemModel = {{ $jsonItem }};
    itemModel.price = parseFloat(itemModel.price);

/*
    if(itemModel.ship_price >= 0 || itemModel.ship_price.length > 0) {
        itemModel.ship_price = parseFloat(itemModel.ship_price);
    }

    var filteredItem = itemModel.filter(function(val) {
        return !(val === "" || typeof val == "undefined" || val === null);
    });

    */
    var openFileBrowser = function () {
        document.getElementById('addFiles').click();
    };

</script>

<script>
    function editController($scope, $http, $upload, $timeout, $rootScope, scopeService) {
        $scope.countries = countries;
        $scope.item = {};
        $scope.item = itemModel;
        $scope.photos = [];
        $scope.dataUrls = [];

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

        $scope.isFileBrowserOpen = false;
        $scope.openFileBrowser = function() {
            if (!$scope.isFileBrowserOpen) {
                $scope.isFileBrowserOpen = true;
                scopeService.safeApply($rootScope, function() {
                    document.getElementById('addFiles').click();
                });
                $scope.isFileBrowserOpen = false;
            }
            console.log('BUTTON CLICKED');

        };

        $scope.tempURL = '';
        $scope.onFileSelect = function($files) {
            //$files: an array of files selected, each file has name, size, and type.
            for (var i = 0; i < $files.length; i++) {
                var file = $files[i];
                if (window.FileReader && file.type.indexOf('image') > -1) {
                    var fileReader = new FileReader();
                    fileReader.readAsDataURL($files[i]);

                    var loadFile = function(fileReader, index) {
                        fileReader.onload = function(e) {
                            $timeout(function() {
                                $scope.dataUrls[index] = e.target.result;
                                console.log(e.target.result);
                                $scope.photos.push({url: e.target.result});
                                $scope.tempURL =  e.target.result;
                            });
                        }
                    }(fileReader, i);
                }

               $scope.tempURL = '"' + $scope.tempURL + '"';
                var newSlide = '<div class="gallery-item"><img draggable="false" src=' + $scope.tempURL + 'class="img-responsive fit-gallery-sq gallery-content" alt="..."></div>'

                $('.img-gallery').slickAdd(newSlide);

                console.log(file);

                $scope.upload = $upload.upload({
                    url: 'server/upload/url', //upload.php script, node.js route, or servlet url
                    // method: 'POST' or 'PUT',
                    // headers: {'header-key': 'header-value'},
                    // withCredentials: true,
                    data: {myObj: $scope.myModelObj},
                    file: file,
                    fileFormDateName: 'photo'
                     // or list of files: $files for html5 only
                    /* set the file formData name ('Content-Desposition'). Default is 'file' */
                    //fileFormDataName: myFile, //or a list of names for multiple files (html5).
                    /* customize how data is added to formData. See #40#issuecomment-28612000 for sample code */
                    //formDataAppender: function(formData, key, val){}
                }).progress(function(evt) {
                        console.log('percent: ' + parseInt(100.0 * evt.loaded / evt.total));
                    }).success(function(data, status, headers, config) {
                        // file is uploaded successfully
                        console.log(data);
                    });
                //.error(...)
                //.then(success, error, progress);
                //.xhr(function(xhr){xhr.upload.addEventListener(...)})// access and attach any event listener to XMLHttpRequest.
            }
            /* alternative way of uploading, send the file binary with the file's content-type.
             Could be used to upload files to CouchDB, imgur, etc... html5 FileReader is needed.
             It could also be used to monitor the progress of a normal http post/put request with large data*/
            // $scope.upload = $upload.http({...})  see 88#issuecomment-31366487 for sample code.
        };

    }
</script>
<div class="form-signin panel wide" data-ng-controller="editController">
    <button class="btn btn-block btn-info" ng-click="buttonPressed()"></button>
    <div class="form-signin-heading h2" id="title-heading" data-ng-model="item.title" contenteditable="true"></div>

        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="add-photos-gallery" ng-file-drop="onFileSelect($files)" ng-file-drag-over-class="dragover" ng-show="dropSupported" onclick="openFileBrowser()">
                    <div class="h3">Add more photos by dropping them here</div>

                    <a href="#" class="btn btn-lg btn-primary" onclick="openFileBrowser()">
                        <span class="glyphicon glyphicon-cloud-upload"></span> Add photo
                        <input type="file" ng-file-select="onFileSelect($files)" accept="image/*" id="addFiles">
                    </a>

                </div>
            </div>
            <div class="col-sm-4 col-lg-4 col-sm-4 col-md-4" data-ng-repeat="photo in photos">
                <div class="thumbnail">
                    <img ng-src="@{{ photo.url }}" class="img-responsive" alt="...">
                </div>
            </div>
        </div>



    <div class="form-group">
        <label for="photos">Images</label>
        <input class="form-control" type="file" multiple="multiple" accept="image/*" name="photos" id="photos" placeholder="Add photos">

        <input type="file" ng-file-select="onFileSelect($files)" accept="image/*">
        <input type="file" ng-file-select="onFileSelect($files)" multiple accept="image/*">
        <div ng-file-drop="onFileSelect($files)" ng-file-drag-over-class="optional-css-class"
             ng-show="dropSupported">drop files here</div>
        <div ng-file-drop-available="dropSupported=true"
             ng-show="!dropSupported">HTML5 Drop File is not supported!</div>
        <button ng-click="upload.abort()">Cancel Upload</button>
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
                <option data-ng-repeat="(code, name) in countries" value="@{{ code }}">@{{ name }}</option>
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
            <a data-ng-click="saveChanges()" class="btn btn-lg pull-right btn-default"><i class="glyphicon glyphicon-upload"></i> Publish</a>
        </div>
    </div>

</div>


@stop
