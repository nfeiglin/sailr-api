var app = angular.module('app', ['angularFileUpload', 'ngAnimate', 'ngSanitize']);

app.controller('rootScopeDefinitionController', ['$rootScope', function ($rootScope) {
    if (baseURL) {
        $rootScope.baseURL = baseURL;
    }

    else {
        $rootScope.baseURL = 'http://sailr.co';
    }
}]);

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

app.directive('sailrFooter', ['$document', function($document) {
    function link(scope, element, attrs) {
        var hasVScroll = document.body.scrollHeight > document.body.clientHeight;


        if (!hasVScroll) {
            element.css({
                position: 'relative'
            });
        }

        else {
            element.css({
                position: 'absolute'
            });
        }

    }

    return {
        link: link
    }
}]);

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
                defered.reject(response);

            }

            else {
                service.token = response;
                defered.resolve(response);

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


app.factory('SubscriptionFactory', function($q, $rootScope, $http) {

    var service = {};
    service.subscriptionURL = baseURL + '/settings/subscription';
    console.log('SUBSCRIPTION URL:: ' + service.subscriptionURL);

    service.sayHello = function () {
        return 'HELLO FROM STRIPE FACTORY';
    };

    service.createSubscription = function (planID, stripeToken) {

        var data = {
            _token: csrfToken,
            stripeToken: stripeToken,
            plan: 'awesome'
        };

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

    service.cancelSubscription = function() {

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

});

app.factory('CommentsFactory', function ($q, $http) {

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


});

app.directive('sailrComments', function () {
    return {
        restrict: 'AE',
        require: '^sailrProductId',
        scope: {
            sailrProductId: '@'
        },
        controller: ['$scope', '$http', 'CommentsFactory', function ($scope, $http, CommentsFactory) {

            $scope.comments = [];
            $scope.commentOpacity = 1.00;
            $scope.webError = false;

            $scope.newComment = {};
            $scope.loggedInUser = loggedInUser;
            $scope.item_id = 0;

            $scope.getComments = function (product_id) {
                //console.log('PRODUCT ID:: ' + product_id);
                $scope.item_id = product_id;


                var getCommentsPromise = CommentsFactory.getComments($scope.item_id);

                getCommentsPromise.then(function (successResponse) {
                        $scope.comments = successResponse.data;
                    },
                    function (failResponse) {
                        console.log(failResponse);
                        $scope.webError = true;
                    });

                };

            $scope.postNewComment = function () {
                var newCommentIndex = $scope.comments.unshift({
                    item_id: $scope.item_id,
                    comment: $scope.newComment.comment,
                    created_at: new Date(),
                    user: loggedInUser
                });

                var newCommentPromise = CommentsFactory.postNewComment($scope.newComment.comment, $scope.item_id);
                console.log(CommentsFactory);
                console.log(newCommentPromise);

                newCommentPromise.then(function (successResponse) {
                        console.log(successResponse);
                        $scope.newComment.comment = '';
                        //Set opacity to 1.00
                    },
                    function (failResponse) {
                        console.log(failResponse);
                        $scope.comments = $scope.comments.splice(1, newCommentIndex);
                    });

            }
        }],

        templateUrl: baseURL + '/js/templates/comments/master.html',
        link: function (scope, iElement, iAttrs, ctrl) {
            scope.getComments(iAttrs.sailrProductId);
            scope.item_id = iAttrs.sailrProductId;
        }
    }
});

app.directive('sailrProductId', function () {
    return {
        controller: function ($scope) {}
    }
});

app.directive('sailrComment', function () {
    return {
        restrict: 'AE',
        scope: {
            profileImageUrl: '@',
            username: '@',
            name: '@',
            commentText: '@'
        },
        controller: ['$scope', function ($scope) {

        }],

        link: function (scope, iElement, iAttrs, ctrl) {
            scope.username = iAttrs.username;
            scope.name = iAttrs.name;
            scope.profileImageUrl = iAttrs.profileImageUrl;
            scope.commentText = iAttrs.commentText;
            scope.baseURL = baseURL;

        },

        templateUrl: baseURL + '/js/templates/comments/comment-item.html'

    }

});


