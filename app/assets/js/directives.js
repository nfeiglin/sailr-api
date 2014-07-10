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

app.directive('focusMe', function ($timeout, $parse) {
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

app.directive('sailrFooter', ['$document', '$window', function ($document, $window) {
    function link(scope, element, attrs) {

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

    return {
        scope: false,
        link: link
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

