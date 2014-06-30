app.controller('manageController', ['$scope', '$http', '$q', 'SubscriptionFactory', '$rootScope', function ($scope, $http, $q, SubscriptionFactory, $rootScope) {
//Code goes here
    $scope.posting = false;
    $scope.subscription = subscription;
    $scope.user = user;

    $scope.cancelSubscription = function () {
        $scope.posting = true;
        var cancelSubscription = SubscriptionFactory.cancelSubscription();

        cancelSubscription.then(function(response) {
            $scope.posting = false;
            $scope.subscription.cancel_at_period_end = true;

            humane.log(response.message);

            //window.location.reload(false);
        },
            function (response) {
                $scope.posting = false;
                $scope.$apply();
                humane.log(response.message)
            }
        );
    }

}]);