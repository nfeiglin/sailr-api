app.controller('chooseController', ['$scope', '$http', '$q', 'StripeFactory', 'HelperFactory', function ($scope, $http, $q, StripeFactory, HelperFactory) {
    $scope.showingCreditForm = false;
    $scope.showCardForm = false;
    $scope.posting = false;
    $scope.card = {};

    $scope.subscribeToPlan = function(planID) {
        console.log('PLAN ID::: ' + planID);
        var expiryArray = HelperFactory.stripWhiteSpace($scope.card.expiry).split('/');
        $scope.card.stripeData = {
            number: HelperFactory.stripWhiteSpace($scope.card.number),
            cvc: HelperFactory.stripWhiteSpace($scope.card.cvc),
            exp_month: expiryArray[0],
            exp_year: expiryArray[1]
        };

        /* If there is a cardholder name, add it to the request to be sent to Stripe */
        if ($scope.card.name && $scope.card.length > 1) {
            $scope.card.stripeData.name = $scope.card.name;
        }

        $scope.posting = true;
        var StripePromise = StripeFactory.createToken($scope.card.stripeData);

        StripePromise.then(function(response)
        {
            console.log(response);
            console.log(StripeFactory.getToken());
            //HTTP CALL HERE
            $scope.posting = false;
        }, function(response)
       {
           $scope.posting = false;
           humane.log(StripeFactory.getErrors().message);
       });

    };

    $scope.handleSubscribeButtonPressed = function() {
        if (!$scope.showingCreditForm) {
            //cardFormContainer.slideToggle();
            $scope.showCardForm = true;
            $scope.showingCreditForm = true;
        }


    }


}]);