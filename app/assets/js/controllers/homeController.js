app.controller('homeController', ['$scope', function ($scope) {

    $scope.numberOfProducts = 3;
    $scope.alreadyLoadedNumber = $scope.numberOfProducts;
    $scope.offsetLoadProducts = 0;
    $scope.loadMoreButtonPressCount = 0;

    $scope.showNowSignupText = false;

    $scope.loadMore = function (numberToLoad) {
        $scope.alreadyLoadedNumber = $scope.numberOfProducts + $scope.offsetLoadProducts;
        $scope.numberOfProducts = numberToLoad;
        $scope.offsetLoadProducts = $scope.alreadyLoadedNumber;

        $scope.loadMoreButtonPressCount++;
        if ($scope.loadMoreButtonPressCount > 2 && !$scope.showNowSignupText) {
            $scope.showNowSignupText = true;
        }
    }


}]);