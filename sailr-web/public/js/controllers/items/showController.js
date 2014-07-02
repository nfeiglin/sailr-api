app.controller('showController', ['$scope', function ($scope) {
    $scope.item = item;
    $scope.user = $scope.item.user;
    $scope.profile_img_url = $scope.user.profile_img.url;
}]);