var file;

app.factory('FileFactory', function($q, $http, $timeout, $upload) {

    var service = {};
    var dataURIString;

    service.tempFiles = [];
    service.getTempFiles = function() {
        return service.tempFiles;
    };

    service.dataURItoBlob = function(dataURI) {
            var binary = atob(dataURI.split(',')[1]);
            var array = [];
           for(var i = 0; i < binary.length; i++) {
                array.push(binary.charCodeAt(i));
            }
            return new Blob([new Uint8Array(array)], {type: 'image/png'});

    };
    service.uploadFile = function($files, postName, uploadUrl, data) {

        console.log($files);
        //$files: an array of files selected, each file has name, size, and type.

            if ($files.length > 1) {
                humane.log('Please only one photo at a time.. We are only new here.');
                return false;
            }
            service.file = $files[0];

            if (window.FileReader && service.file.type.indexOf('image') > -1) {
                var fileReader = new FileReader();
                fileReader.readAsDataURL(service.file);
                var tooBig = false;
                if (service.file.size > 7340032) {
                    tooBig = true;
                    console.log('File too large.');
                    humane.log('File is too large. Please try compressing it first.');
                    return;
                }
                console.log('not too big');
                var loadFile = function(fileReader, index) {
                    fileReader.onload = function(e) {
                        $timeout(function() {
                            console.log(e.target.result);
                            dataURIString = e.target.result;

                            service.base64String = e.target.result;
                            service.tempFiles.push({
                                dataUrl: e.target.result
                            });

                            return service.doUpload(postName, uploadUrl, data);


                        });
                    }
                }(fileReader, 0);
            }


    };

    service.doUpload = function(postName, uploadUrl, data) {
        if (!postName) {
            postName = 'photos';
        }
        var defered = $q.defer();
        if (!data) {
            data = {};
        }
        data._token = csrfToken;

        console.log(uploadUrl);

        console.log('IN DO UPLOAD');
        console.log(service.file);
        console.log(service.base64String);
        console.log('^^^ Base 64 String');
        var file = service.dataURItoBlob(service.base64String);
        console.log(file);




       service.uploadPromise = $upload.upload({
       url: uploadUrl, //upload.php script, node.js route, or servlet url
       ///method: 'POST' or 'PUT',
        //headers: {'header-key': 'header-value'},
            // withCredentials: true,
            data: data,
            file: file,
            fileFormDateName: postName
            // or list of files: $files for html5 only
            /* set the file formData name ('Content-Desposition'). Default is 'file' */
            //fileFormDataName: myFile, //or a list of names for multiple files (html5).
            /* customize how data is added to formData. See #40#issuecomment-28612000 for sample code */
            //formDataAppender: function(formData, key, val){}
       }).progress(function(evt) {
           defered.notify(parseInt(100.0 * evt.loaded / evt.total));
            console.log('percent: ' + parseInt(100.0 * evt.loaded / evt.total));
       }).success(function(data, status, headers, config) {
           console.log(data);
           console.log(status);
           console.log(headers);

            defered.resolve(data);
            service.uploading = false;
        }).error(function(data, status, headers, config) {
           console.log(data);
           console.log(status);
           console.log(headers);

           service.uploading = false;
            defered.reject(data);
        });
        return defered;
    };

    return service;
});