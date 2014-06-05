app.controller('feedController', ['$scope', '$http', function ($scope, $http) {
    $scope.baseURL = baseURL;
    $scope.submitSearchForm = function() {
        window.location = $scope.baseURL + '/s/' + encodeURIComponent($scope.searchText);
    };

}]);