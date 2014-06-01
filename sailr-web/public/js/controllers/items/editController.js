
/*
 if(itemModel.ship_price >= 0 || itemModel.ship_price.length > 0) {
 itemModel.ship_price = parseFloat(itemModel.ship_price);
 }

 var filteredItem = itemModel.filter(function(val) {
 return !(val === "" || typeof val == "undefined" || val === null);
 });

 */
itemModel.price = parseFloat(itemModel.price);
itemModel.initial_units = parseFloat(itemModel.initial_units);
itemModel.ship_price = parseFloat(itemModel.ship_price);
itemModel.public = parseInt(itemModel.public);

var openFileBrowser = function () {
    document.getElementById('addFiles').click();
};

app.controller('editController', ['$scope', '$http', '$upload', '$timeout', '$filter', function ($scope, $http, $upload, $timeout, $filter) {

    $scope.countries = countries;
    $scope.currencies = currencies;
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
                $scope.item.description = data.description;
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
    $scope.uploading = false;
    $scope.onFileSelect = function ($files) {


        if ($scope.uploading) {
            humane.log('One at a time please.');
            return;
        }
        //$files: an array of files selected, each file has name, size, and type.
        for (var i = 0; i < $files.length; i++) {
            if ($files.length > 1) {
                humane.log('Please only one photo at a time.. We are only new here.');
                break;
            }
            var file = $files[i];
            if (window.FileReader && file.type.indexOf('image') > -1) {
                var fileReader = new FileReader();
                fileReader.readAsDataURL($files[i]);

                var tooBig = false;
                if (file.size > 7340032) {
                    tooBig = true;
                    console.log('File too large.');
                    humane.log('File is too large. Please try compressing it first.');
                    return;
                }


                    console.log('not too big');
                    var loadFile = function (fileReader, index) {
                        fileReader.onload = function (e) {
                            $timeout(function () {
                                $scope.showCropBox(e);
                                $scope.dataUrls[index] = e.target.result;
                                console.log(e.target.result);
                                $scope.photos.push({url: e.target.result});
                                $scope.tempURL = e.target.result;
                            });
                        }
                    }(fileReader, i);


            }


            console.log(file);


            humane.log('Uploading...');
            $scope.uploading = true;

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
                $scope.photos[$scope.photos.length -1].set_id = data.set_id;
                $scope.photos[$scope.photos.length -1].url = data.url;
                // file is uploaded successfully
                humane.log('Photo successfully uploaded!');
                console.log(data);
                console.log(status);
                console.log(headers);
                $scope.uploading = false;
            })
                .error(function (data, status, headers, config) {
                    $scope.photos = $scope.photos.slice($scope.photos.length -1, 1);
                    humane.log('Upload failed. ' + data);
                    console.log(data);
                    console.log(status);
                    console.log(headers);
                    $scope.uploading = false;
                });

        }

    };

    $scope.showCropBox = function(e) {
        //$('#crop-modal').modal('show');
        var image = new Image();
        image.src = e.target.result;

        image.onload = (function() {
            var canvas = document.createElement('canvas');
            canvas.width = 300;
            canvas.height = image.height * (300 / image.width);
            var ctx = canvas.getContext('2d');
            ctx.drawImage(image, 0, 0, canvas.width, canvas.height);

            $('#image_input').html(['<img src="', canvas.toDataURL(), '"/>'].join(''));

            var img = $('#image_input img')[0];
            var canvas = document.createElement('canvas');

            $('#image_input img').Jcrop({
                bgColor: 'black',
                bgOpacity: .6,
                setSelect: [0, 0, 100, 100],
                aspectRatio: 1,
                //minSize: [150, 150],
                keySupport: false,
                //onSelect: imgSelect,
                onChange: imgSelect
            });


            function imgSelect(selection) {
                canvas.width = canvas.height = 100;

                var ctx = canvas.getContext('2d');
                ctx.drawImage(img, selection.x, selection.y, selection.w, selection.h, 0, 0, canvas.width, canvas.height);

                $('#image_output').attr('src', canvas.toDataURL());
                $('#image_source').text(canvas.toDataURL());
            }
        });

        };

    $scope.deletePhoto = function($index) {
        var photo = $scope.photos[$index];
        var set_id = photo.set_id;
        photo.deleted = true;

        var deleteImageUrl = baseURL + '/photo/' + $scope.item.id;
        var data = {set_id: set_id, _token: csrfToken, _method: 'DELETE'};

        humane.log('Deleting...');
        $http.put(deleteImageUrl, JSON.stringify(data))
            .success(function (data, status, headers, config) {
                $scope.responseData = data;
                console.log('The response data is: ');
                console.log($scope.responseData);

                humane.log('Photo deleted successfully');
                $scope.photos.splice($index, 1);

            })

            .error(function (data, status, headers, config) {
                console.log(data);
                humane.log('Photo deletion failed.');
                photo.deleted = false;

            });
    };


}]);