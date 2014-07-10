app.factory('OnboardFactory',['$http',  function($http){
    var service = {};

    service.getRecentProducts = function (offset, limit) {
        console.log('OFFSET IS ::' + offset);
        console.log('LIMIT IS:: ' + limit);
        var url = baseURL + '/onboard/recent/products/' + offset + '/' + limit;
        return $http.get(url);
    };

    return service;
}]);

app.factory('StripeFactory',['$q', function ($q) {
    var service = {};

    service.sayHello = function () {
        return 'HELLO FROM STRIPE FACTORY';
    };

    service.setPublishableKey = function (key) {
        Stripe.setPublishableKey(key);
    };


    service.createToken = function (cardData) {
        var defered = $q.defer();

        Stripe.card.createToken(cardData, function (status, response) {

            if (response.error) {
                service.errors = response.error;
                defered.reject(response);

            }

            else {
                service.token = response;
                defered.resolve(response);

            }
        });

        return defered.promise;
    };

    service.getToken = function () {
        return service.token;
    };

    service.getErrors = function () {
        return service.errors;
    };

    return service;
}]);

app.factory('HelperFactory', function () {
    var service = {};

    service.stripWhiteSpace = function (string) {
        string = string.replace(/\s/g, "");
        return string;
    };

    service.createStripeCardObjectFromFormattedInput = function (inputObject) {

        var returnCard = {};
        var expiryArray = service.stripWhiteSpace(inputObject.expiry).split('/');

        returnCard = {
            number: service.stripWhiteSpace(inputObject.number),
            cvc: service.stripWhiteSpace(inputObject.cvc),
            exp_month: expiryArray[0],
            exp_year: expiryArray[1]
        };

        /* If there is a cardholder name, add it to the card object */
        if (typeof inputObject.name !== 'undefined') {
            if (inputObject.name.length > 0) {
                returnCard.name = inputObject.name;
            }
        }

        return returnCard;

    };

    return service;
});


app.factory('SubscriptionFactory', ['$q', '$rootScope', '$http', function ($q, $rootScope, $http) {

    var service = {};
    service.subscriptionURL = baseURL + '/settings/subscription';
    console.log('SUBSCRIPTION URL:: ' + service.subscriptionURL);

    service.sayHello = function () {
        return 'HELLO FROM STRIPE FACTORY';
    };

    service.createSubscription = function (planID, stripeToken, couponCode) {

        var data = {
            _token: csrfToken,
            stripeToken: stripeToken,
            plan: 'awesome'
        };

        if(typeof couponCode !== 'undefined') {
            if(couponCode.length > 0) {
                data.coupon = couponCode;
            }
        }

        var defered = $q.defer();

        $http.post(service.subscriptionURL, data)
            .success(function (data, status) {
                defered.resolve(data);
            })

            .error(function (data, status, headers, config) {
                var rejectObject = {
                    data: data,
                    status: status,
                    headers: headers,
                    config: config
                };

                defered.reject(rejectObject);

            });

        return defered.promise;
    };

    service.cancelSubscription = function () {

        var configObject = {
            _token: csrfToken
            //_method: 'delete'
        };

        var defered = $q.defer();

        $http.post(service.subscriptionURL + '/delete', configObject)
            .success(function (data, status) {
                defered.resolve(data);
            })
            .error(function (data, status) {
                defered.reject(data);

            });

        return defered.promise;

    };

    return service;

}]);

app.factory('CommentsFactory',['$q', '$http', function ($q, $http) {

    var service = {};

    service.sayHello = function () {
        console.log('Hello from CommentsFactory');
        return 'Hello from CommentFactory';
    };

    service.postNewComment = function (commentText, productID) {
        console.log('Add new comment function called');
        var postObject = {
            _token: csrfToken,
            comment: commentText,
            item_id: productID
        };

        var defered = $q.defer();

        console.log('BASE URL:: ' + baseURL);
        console.log(postObject);

        $http.post(baseURL + '/comments', postObject)
            .success(function (data, status) {
                defered.resolve(data);
            })
            .error(function (data, status) {
                defered.reject(data);
            });

        return defered.promise;

    };

    service.getComments = function (productID) {
        var defered = $q.defer();

        $http.get(baseURL + '/username/product/' + productID + '/' + 'comments')
            .success(function (data, status) {
                defered.resolve(data);
            })
            .error(function (data, status) {
                defered.reject(data);
            });

        return defered.promise;

    };


    return service;


}]);
