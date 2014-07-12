var app = angular.module('app', ['angularFileUpload', 'ngSanitize']);

app.run(['$rootScope', function ($rootScope) {
    if (baseURL) {
        $rootScope.baseURL = baseURL;
    }
    else {
        $rootScope.baseURL = 'https://sailr.co';
    }

    $rootScope.loggedInUser = loggedInUser;
}]);

var openFileBrowser = function () {
    document.getElementById('addFiles').click();
};