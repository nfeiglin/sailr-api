app.controller('homeController', ['$scope', '$interval', function ($scope, $interval) {

    $scope.theWord = 'fashion';

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
    };

    var i = 0;
    var length = words.length;
    var changeWord = $('#changeWord');

    $scope.doWordChange = function() {
       var intervalPromise = $interval(function() {
           changeWord.removeClass('animate-title-text-in');
            var word = words[i];
           $scope.theWord = word;

            changeWord.animate({'opacity': 0}, 600, function () {
                $(this).addClass('animate-title-text-in');
                $(this).text(word);
            }).animate({'opacity': 1}, 600);

            //console.log(word);
            i++;

            if (i >= length) {
                i = 0;
            }
        }, 2500);

        $scope.$on('$destroy', function () { $interval.cancel(intervalPromise); });
    };

    $scope.doWordChange();


}]);