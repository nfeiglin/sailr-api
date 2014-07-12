/*
 var stripWhiteSpace = function (string) {
 string = string.replace(/\s/g, "");
 return string;
 };
 */

app.controller('billingController', ['$scope', '$http', 'HelperFactory', 'StripeFactory', function ($scope, $http, HelperFactory, StripeFactory) {

    $scope.showUpdateCard = false;
    $scope.baseURL = baseURL;
    $scope.card = {};
    $scope.token = {};
    $scope.posting = false;
    $scope.card.name = loggedInUser.name;
    $scope.card.last4 = last4;
    $scope.card.type = cardType;

    $scope.subscription = subscription;

    $scope.updateCard = function () {
        $scope.posting = true;

        var stripeCard = HelperFactory.createStripeCardObjectFromFormattedInput($scope.card);

        var StripePromise = StripeFactory.createToken(stripeCard);
        StripePromise.then(function (response) {
            console.log(response);
            console.log('SUCCESS!');
            //console.log('TOKEN:::: ' + JSON.stringify(StripeFactory.getToken()));
            console.log(StripeFactory.getToken());
            $scope.token = StripeFactory.getToken();
            $scope.token.id = StripeFactory.getToken().id;
            $scope.submitUpdate();


        }, function (response) {
            $scope.posting = false;
            console.log('Stripe card fail::::');
            console.log(response);
            humane.log(StripeFactory.getErrors().message);

        });

    };

    $scope.submitUpdate = function () {
        $scope.posting = true;
        var data = {
            'stripeToken': $scope.token.id,
            '_token': csrfToken
        };


        $http.put($scope.baseURL + '/settings/billing', data)
            .success(function (data, status, headers, config) {
                $scope.posting = false;
                humane.log(data.message);


                $scope.card.last4 = $scope.token.card.last4;
                $scope.card.type = $scope.token.card.type;


                if ($scope.showUpdateCard) {
                    $scope.showUpdateCard = false;
                }
            }).
            error(function (data, status, headers, config) {
                $scope.posting = false;
                if (!data) {
                    humane.log("We're afraid something went wrong and the card didn't update");
                }
                else {
                    humane.log(data.message);
                }
                if ($scope.showUpdateCard) {
                    $scope.showUpdateCard = false;
                }
            });

    };

    $scope.toggleShowingForm = function () {
        $scope.showUpdateCard = !$scope.showUpdateCard;
    }


}]);

