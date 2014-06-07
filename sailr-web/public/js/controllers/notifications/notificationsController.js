app.controller('notificationsController', ['$scope', '$http', function ($scope, $http) {
    $scope.notifications = sailr.notifications;
    $scope.baseURL = baseURL;

}]);