var openFileBrowser = function () {
    document.getElementById('addFiles').click();
};


app.controller('updateController', ['$scope', function ($scope) {

    $scope.showSubmit = false;
    $('#addFiles').on('change', function() {
        $scope.$apply(function() {
           $scope.showSubmit = true;
        });
    });

    $scope.user = {};
    $scope.fileButtonText = 'Select new profile photo';

    $scope.user = loggedInUser;
    $scope.profileURL = profileImageURL;


}]);