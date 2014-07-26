app.controller('homeController', ['$scope', '$interval', function ($scope, $interval) {

    var words = [
        'bags',
        'shoes',
        'makeup',
        'fashion',
        'jewellery',
        'accessories'
    ];

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
    var changeWord = document.getElementById('changeWord');

    $scope.doWordChange = function() {
        $interval(function() {
            var word = words[i];
           $scope.theWord = word;
            $('#changeWord').fadeIn().text(word);
            //document.getElementById('changeWord').innerHTML(word);
            console.log(word);
            i++;

            if (i >= length) {
                i = 0;
            }
        }, 3000);
    };

    $scope.doWordChange();

}]);