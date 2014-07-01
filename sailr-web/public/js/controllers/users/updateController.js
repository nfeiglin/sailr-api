var openFileBrowser = function () {
    document.getElementById('addFiles').click();
};

app.controller('updateController', ['$scope', '$http', 'FileFactory', function ($scope, $http, FileFactory) {
    $scope.user = {};
    $scope.user = loggedInUser;
    $scope.user.name = 'ZZZZZZZ';
    console.log($scope.user);

$scope.onFileSelect = function($files) {

    var uploadPromise = FileFactory.uploadFile($files, 'photos', baseURL + '/settings/user/profile-image', {});
    $scope.profileURL = profileImageURL;

    uploadPromise.then(function(successResponse) {

        console.log(successResponse);
        angular.forEach(successResponse.profile_img, function(value, key) {
           if (key == 'small') {
               $scope.profileURL = value;
           }
        });

        humane.log('Profile image updated')

    },
    function(failResponse) {

    },
    function (notificationEvent) {
        console.log(FileFactory.getTempFiles());
    });

}
}]);