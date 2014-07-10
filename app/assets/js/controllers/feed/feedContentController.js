app.controller('feedContentController', ['$scope', function ($scope) {
    $scope.numberOfProducts = 6;
    $scope.alreadyLoadedNumber = $scope.numberOfProducts;
    $scope.offsetLoadProducts = 0;

    $scope.loadMoreRecentProducts = function (numberToLoad) {
        $scope.alreadyLoadedNumber = $scope.numberOfProducts + $scope.offsetLoadProducts;
        $scope.numberOfProducts = numberToLoad;
        $scope.offsetLoadProducts = $scope.alreadyLoadedNumber;
    }



}]);