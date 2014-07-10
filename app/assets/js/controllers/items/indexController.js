app.controller('indexController', ['$scope', '$http', function($scope, $http){

    $scope.currency = 'USD';
    $scope.codes = currencyCodes;

    $scope.handleCodeChange = function ($index) {
        $scope.currency = $scope.codes[$index];
        console.log($scope.currency);
    };

    $scope.toggleAdd = function () {
        $scope.shouldShowAdd = !$scope.shouldShowAdd;
        $('#addItem').slideToggle(300);
        console.log('Show pressed');
    };

    document.scope = $scope;

    $scope.formSubmit = function () {
        $scope.posting = true;
        $scope.formData = {_token: csrfToken, title: $scope.title, currency: $scope.currency, price: $scope.price};
        console.log($scope.formData);

        $http.post('/products', JSON.stringify($scope.formData))
            .success(function (data, status, headers, config) {
                //console.log('the data to be sent is ' + JSON.stringify(data));
                $scope.responseData = data;
                //console.log($scope.responseData);

                if (data.message) {
                    $scope.posting = false;
                    humane.log(data.message);
                }

                if (data.redirect_url) {
                    window.location = $scope.responseData.redirect_url;
                }

                $scope.posting = false;
            })

            .error(function (data, status, headers, config) {
                console.log(data);
                $scope.posting = false;

            });
    };

}]);
