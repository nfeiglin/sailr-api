var app = angular.module('app', ['angularFileUpload', 'ngAnimate']);

app.directive('contenteditable', function () {
    return {
        restrict: 'A', // only activate on element attribute
        require: '?ngModel', // get a hold of NgModelController
        link: function (scope, element, attrs, ngModel) {
            if (!ngModel) return; // do nothing if no ng-model

            // Specify how UI should be updated
            ngModel.$render = function () {
                element.html(ngModel.$viewValue);
            };

            // Listen for change events to enable binding
            element.on('blur keyup change input', function () {
                scope.$apply(read);
            });
            read(); // initialize

            // Write data to the model
            function read() {
                var html = element.html();
                // When we clear the content editable the browser leaves a <br> behind
                // If strip-br attribute is provided then we strip this out
                if (attrs.stripBr && html == '<br>') {
                    html = '';
                }
                ngModel.$setViewValue(html);
            }
        }
    };
});

app.directive('num-binding', function () {
    return {
        restrict: 'A',
        require: 'ngModel',
        scope: {
            model: '=ngModel',
        },
        link: function (scope, element, attrs, ngModelCtrl) {
            if (scope.model && typeof scope.model == 'string') {
                scope.model = parseInt(scope.model);
            }
        }
    };
});

app.directive('focusMe', function($timeout, $parse) {
    return {
        //scope: true,   // optionally create a child scope
        link: function(scope, element, attrs) {
            var model = $parse(attrs.focusMe);
            scope.$watch(model, function(value) {
                console.log('value=',value);
                if(value === true) {
                    $timeout(function() {
                        element[0].focus();
                    });
                }
            });
            /*
            // to address @blesh's comment, set attribute value to 'false'
            // on blur event:
            element.bind('blur', function() {
                console.log('blur');
                scope.$apply(model.assign(scope, false));
            });
            */
        }
    };
});


app.factory('StripeFactory', function($q, $rootScope) {
   var service = {};

    service.sayHello = function() {
        return 'HELLO FROM STRIPE FACTORY';
    };

    service.setPublishableKey = function(key) {
        Stripe.setPublishableKey(key);
    };


    service.createToken = function(cardData) {
        var defered = $q.defer();
        Stripe.card.createToken(cardData, function(status, response) {

            if (response.error) {
                service.errors = response.error;
                $rootScope.$apply(function(){
                    defered.reject(response);
                });

            }

            else {
                service.token = response;
                $rootScope.$apply(function(){
                    defered.resolve(response);
                });

            }
        });

        return defered.promise;
    };

    service.getToken = function() {
        return service.token;
    };

    service.getErrors = function() {
        return service.errors;
    };

    return service;
});

app.factory('HelperFactory', function($http) {
    var service = {};

    service.stripWhiteSpace = function (string) {
        string = string.replace(/\s/g, "");
        return string;
    };

    service.createStripeCardObjectFromFormattedInput = function(inputObject) {

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


    return service;
});
