app.controller('collectionsIndexController', ['$scope', '$http', function ($scope, $http) {

    var container = document.querySelector('#masonryContainer');
    var msnry = new Masonry(container, {
        columnWidth: 220,
       itemSelector: '.collection-item'
    });
    $scope.collections = {};
    $scope.loading = true;
    $scope.message = '';
    $scope.username = username;

    $scope.getCollections = function() {
      $http.get(baseURL + '/api/collections/' + username + '/all')
          .success(function(data) {
              $scope.loading = false;
             $scope.collections = data.collections;
          })
          .error(function(data) {
              $scope.loading = false;
            $scope.message = data.error;
          });
    };

    $scope.getCollections();
}]);

