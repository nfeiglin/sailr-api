app.controller('notificationsController', ['$scope', function ($scope) {
    $scope.notifications = sailr.notifications;
    $scope.baseURL = baseURL;

}]);