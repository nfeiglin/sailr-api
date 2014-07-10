var app = angular.module('app', ['angularFileUpload', 'ngSanitize']);

app.run(function ($rootScope) {
    if (baseURL) {
        $rootScope.baseURL = baseURL;
    }

    else {
        $rootScope.baseURL = 'https://sailr.co';
    }

    $rootScope.loggedInUser = loggedInUser;
});