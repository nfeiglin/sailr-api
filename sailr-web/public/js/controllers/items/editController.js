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

app.controller('editController', ['$scope', '$http', '$upload', '$timeout', function ($scope, $http, $upload, $timeout) {

    $scope.countries = countries;
    $scope.item = {};
    $scope.item = itemModel;
    $scope.photos = [];
    $scope.dataUrls = [];

    if("photos" in $scope.item) {
        $scope.photos = $scope.item.photos;
    }

    /* Set the inital values for these fields as the data binding is a bit dodgy */
    document.getElementById('title-heading').innerText = $scope.item.title;
    document.getElementById('item-description').innerHTML = $scope.item.description;
    // document.getElementById('item-price').value = $scope.item.price;
    document.scope = $scope;

    $scope.buttonPressed = function () {
        console.log($scope.desc);
        console.log($scope);
    };

    $scope.saveChanges = function () {
        $scope.posting = true;

        var data = $scope.item;
        data._token = sessionToken;
        console.log('the data to be sent is ' + JSON.stringify(data));

        $http.put(updateURL, JSON.stringify(data))
            .success(function (data, status, headers, config) {
                $scope.responseData = data;
                console.log('The response data is: ');
                console.log($scope.responseData);
                //window.location = $scope.responseData.redirect_url;
                $scope.posting = false;
            })

            .error(function (data, status, headers, config) {
                console.log(data);
                $scope.posting = false;

            });

    };

    $scope.publish = function () {

    };

    $scope.isFileBrowserOpen = false;
    $scope.openFileBrowser = function () {
        if (!$scope.isFileBrowserOpen) {
            $scope.isFileBrowserOpen = true;
            document.getElementById('addFiles').click();
            $scope.isFileBrowserOpen = false;
        }
        console.log('BUTTON CLICKED');

    };

    $scope.tempURL = '';
    $scope.onFileSelect = function ($files) {
        //$files: an array of files selected, each file has name, size, and type.
        for (var i = 0; i < $files.length; i++) {
            var file = $files[i];
            if (window.FileReader && file.type.indexOf('image') > -1) {
                var fileReader = new FileReader();
                fileReader.readAsDataURL($files[i]);

                var loadFile = function (fileReader, index) {
                    fileReader.onload = function (e) {
                        $timeout(function () {
                            $scope.dataUrls[index] = e.target.result;
                            console.log(e.target.result);
                            $scope.photos.push({url: e.target.result});
                            $scope.tempURL = e.target.result;
                        });
                    }
                }(fileReader, i);
            }


            console.log(file);

            $scope.upload = $upload.upload({
                url: baseURL + '/photo/upload/' + itemModel.id, //upload.php script, node.js route, or servlet url
                // method: 'POST' or 'PUT',
                // headers: {'header-key': 'header-value'},
                // withCredentials: true,
                data: {_token: csrfToken},
                file: file,
                fileFormDateName: 'photo'
                // or list of files: $files for html5 only
                /* set the file formData name ('Content-Desposition'). Default is 'file' */
                //fileFormDataName: myFile, //or a list of names for multiple files (html5).
                /* customize how data is added to formData. See #40#issuecomment-28612000 for sample code */
                //formDataAppender: function(formData, key, val){}
            }).progress(function (evt) {
                console.log('percent: ' + parseInt(100.0 * evt.loaded / evt.total));
            }).success(function (data, status, headers, config) {
                // file is uploaded successfully
                console.log(data);
                console.log(status);
                console.log(headers);
            })
                .error(function (data, status, headers, config) {
                    console.log(data);
                    console.log(status);
                    console.log(headers);
                });
            //.then(success, error, progress);
            //.xhr(function(xhr){xhr.upload.addEventListener(...)})// access and attach any event listener to XMLHttpRequest.
        }
        /* alternative way of uploading, send the file binary with the file's content-type.
         Could be used to upload files to CouchDB, imgur, etc... html5 FileReader is needed.
         It could also be used to monitor the progress of a normal http post/put request with large data*/
        // $scope.upload = $upload.http({...})  see 88#issuecomment-31366487 for sample code.
    };

}]);