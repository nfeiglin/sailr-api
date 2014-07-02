app.controller('chooseController', ['$scope', '$http', '$q', 'StripeFactory', 'HelperFactory', 'SubscriptionFactory', function ($scope, $http, $q, StripeFactory, HelperFactory, SubscriptionFactory) {

    $scope.showingCreditForm = false;
    $scope.showCardForm = false;
    $scope.posting = false;
    //$scope.successSubscribe = false;
    $scope.card = {};

    $scope.subscribeToPlan = function(planID) {
        console.log('PLAN ID::: ' + planID);

        $scope.posting = true;

        var stripeCard = HelperFactory.createStripeCardObjectFromFormattedInput($scope.card);

        var StripePromise = StripeFactory.createToken(stripeCard);

        StripePromise.then(function(response)
        {
            console.log(response);
            console.log(StripeFactory.getToken());

            var createSubscriptionPromise = SubscriptionFactory.createSubscription(planID, StripeFactory.getToken().id);

            createSubscriptionPromise.then(function(responseObject) {

                $scope.posting = false;
                $scope.showCardForm = false;
                //Success!
                console.log('SUCCESS on server subscription create');
                console.log(responseObject);
                humane.log(responseObject.message);
                window.location =  responseObject.redirect_url;
            },

            function(responseObject) {
                //Fail :(
                $scope.posting = false;
                $scope.$apply(function() {
                    $scope.posting = false;
                });

                console.log('Subscription fail::   --');
                console.log(responseObject);
                humane.log(responseObject.message);

            });


        }, function(response)
       {
           $scope.posting = false;
           console.log('Stripe card fail::::');
           console.log(response);

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