var App = angular.module("fhs-search", ["ngRoute"]);

angular.module('fhs-search').controller('SearchCtrl', function ($scope, $http, $location) {
  var url = "backend/interface/query.php";
  $scope.persons = null;

  $scope.getPersons = function () {
    if ($scope.query.length == 0) {
      $location.search("query", null);
    } else {
      $location.search("query", $scope.query);
    }

    if (String.toLowerCase($scope.query).indexOf("fhs") > -1 && $scope.query.length < 5) {
      $scope.persons = null;
      return;
    } else if ($scope.query.length < 3) {
      $scope.persons = null;
      return;
    }

    $http.post(url, {"data": $scope.query}).
      success(function (data, status) {
        $scope.persons = data;
      })
      .error(function (data, status) {
        $scope.data = data || "Request failed";
        $scope.status = status;
        console.log("error", status, data);
      });
  };

  if($location.search()){
    var query = $location.search().query || "";
    $scope.query = query;
    $scope.getPersons();
  }
});

App.config(['$routeProvider',
  function ($routeProvider) {
    $routeProvider.when('*', {reloadOnSearch: false, controller: "SearchCtrl"});
  }
]);