var stripWhiteSpace = function (string) {
    string = string.replace(/\s/g, "");
    return string;
};

app.controller('billingController', ['$scope', '$http', function ($scope, $http) {
    $scope.showUpdateCard = false;
    $scope.baseURL = baseURL;
    $scope.card = {};
    $scope.token = {};
    $scope.posting = false;

    $scope.card.last4 = last4;
    $scope.card.type = cardType;

    $scope.updateCard = function () {

        var expiryArray = stripWhiteSpace($scope.card.expiry).split('/');


        Stripe.card.createToken({
            number: stripWhiteSpace($scope.card.number),
            cvc: stripWhiteSpace($scope.card.cvc),
            exp_month: expiryArray[0],
            exp_year: expiryArray[1]
        }, stripeResponseHandler);


        function stripeResponseHandler(status, response) {
            $scope.posting = false;
            if (response.error) {

                humane.log(response.error.message);

            } else {
                $scope.posting = true;
                // token contains id, last4, and card type
                $scope.token = response;
                $scope.submitUpdate();

            }
        }


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


}]);

