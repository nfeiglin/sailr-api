app.controller('searchController', ['$scope', '$http', function ($scope, $http) {
    $scope.results = sailr.results;
    $scope.baseURL = baseURL;

}]);