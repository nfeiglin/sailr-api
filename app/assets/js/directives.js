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
            model: '=ngModel'
        },
        link: function (scope, element, attrs, ngModelCtrl) {
            if (scope.model && typeof scope.model == 'string') {
                scope.model = parseInt(scope.model);
            }
        }
    };
});


app.directive('sailrFooter', ['$document', '$window', function ($document, $window) {
    return {
        scope: false,
        link: function(scope, element, attrs) {

            var positionFooter = function() {
                var hasVScroll = document.body.scrollHeight | document.body.clientHeight > $window.innerHeight;
                //console.log(element.style.height);

                var newTop = $window.document.body.scrollHeight;// + element.style.height;
                var stringNewHeight = newTop + 'px';
                if (hasVScroll) {
                    element.css({
                        position: 'relative',
                        top: '100%'
                    });
                }

                else {
                    element.css({
                        position: 'absolute',
                        top: stringNewHeight
                    });
                }
            };

            positionFooter();

            scope.$watch($window.innerHeight, function(newValue, oldValue) {
                positionFooter();

            });


        }
    }


}]);

app.directive('sailrOpactiy', function () {

    function link(scope, iElement, iAttrs) {

        scope.sailrOpacity = iAttrs.sailrOpacity;
        iElement.css({
            opacity: scope.sailrOpacity
        });
    }

    return {
        restrict: 'A',
        scope: {
            sailrOpacity: '@'
        },
        link: link
    }
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
        link: function (scope, iElement, iAttrs) {
            scope.getComments(iAttrs.sailrProductId);
            scope.item_id = iAttrs.sailrProductId;
        }
    }
});

app.directive('sailrProductId', function () {
    return {
        controller: ['$scope', function ($scope) {}]
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

        link: function (scope, iElement, iAttrs) {
            scope.username = iAttrs.username;
            scope.name = iAttrs.name;
            scope.profileImageUrl = iAttrs.profileImageUrl;
            scope.commentText = iAttrs.commentText;
            scope.baseURL = baseURL;

        },

        templateUrl: baseURL + '/js/templates/comments/comment-item.html'

    }

});

app.directive('sailrEntityLink', function() {
    return {
        priority: -1,
        restrict: 'AE',
        scope: false,
        link: function(scope, iElement, iAttrs) {
            scope.$watch(iAttrs.sailrEntityLink, function(newValue, oldValue) {
                var tempHTML = iElement.html();
                iElement.html(twttr.txt.autoLink(tempHTML));
            });

        }
    }
});

app.directive('sailrFeedOnboardBox', function() {
    return {
        restrict: 'E',
        scope: false,
        templateUrl: baseURL + '/js/templates/onboard/feed/onboard-box.html'
    }
});

app.directive('sailrNumberOfProducts', function() {
    return {
        restrict: 'A',
        controller: ['$scope', function($scope){}]
    }
});

app.directive('sailrOffsetBy', function() {
    return {
        restrict: 'A',
        controller: ['$scope', function($scope){}]
    }
});

app.directive('sailrProductPreview', function() {
    return {
        restrict: 'AE',
        scope: {
            productTitle: '@',
            productPreviewImageUrl: '@',
            productLinkUrl: '@',
            productSellerUsername: '@',
            productSellerName: '@',
            productSellerUrl: '@'
        },

        templateUrl: baseURL + '/js/templates/onboard/recent/products/product-preview.html'
    }
});

app.directive('sailrRecentProducts', function() {
    return {
        restrict: 'AE',
        require: ['sailrNumberOfProducts', 'sailrOffsetBy'],
        scope: {
            sailrNumberOfProducts: '@',
            sailrOffsetBy: '@'

        },

        controller: ['$scope', '$http', 'OnboardFactory', function ($scope, $http, OnboardFactory) {

            $scope.baseURL = baseURL;
            $scope.products = [];
            $scope.initialValue = 00;

            $scope.getProducts = function (offset, limit) {

                OnboardFactory.getRecentProducts(offset, limit)
                    .success(function(data, status, headers) {
                        /*append all the things */
                        angular.forEach(data, function (value) {
                            $scope.products.push(value);
                        });

                        //console.log('NUMBER OF ELEMENTS IN ARRAY:: ' + $scope.products.length);

                    })
                    .error(function(data, status, headers) {
                        $scope.webError = true;
                        //console.log(data);
                    });

            };

        }],

        templateUrl: baseURL + '/js/templates/onboard/recent/products/master.html',
        link: function(scope, elem, attrs) {

            var loadProducts = function(numberToLoad, offset) {
                scope.getProducts(offset, numberToLoad);
                return true;
            };

            scope.$watch(function() {
                return [attrs.sailrNumberOfProducts, attrs.sailrOffsetBy];
            }, function() {
                loadProducts(scope.sailrNumberOfProducts, scope.sailrOffsetBy);
            }, true);

        }

    }
});

app.directive('focusMe',['$timeout', '$parse', function ($timeout, $parse) {
    return {
        //scope: true,   // optionally create a child scope
        link: function (scope, element, attrs) {
            var model = $parse(attrs.focusMe);
            scope.$watch(model, function (value) {
                console.log('value=', value);
                if (value === true) {
                    $timeout(function () {
                        element[0].focus();
                    });
                }
            });
        }
    };
}]);
