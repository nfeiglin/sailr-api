app.controller('manageController', ['$scope', '$http', '$q', 'SubscriptionFactory', function ($scope, $http, $q, SubscriptionFactory) {
//Code goes here
    $scope.posting = false;

    $scope.cancelSubscription = function () {
        $scope.posting = true;
        var cancelSubscription = SubscriptionFactory.cancelSubscription();

        cancelSubscription.then(function(response) {
            humane.log(response.message);
        },
            function (response) {
                humane.log(response.message)
            }
        );
    }

}]);