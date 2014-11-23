var App = angular.module("fhs-search", ["ngRoute"]);
l = console.log;

angular.module('fhs-search').controller('SearchCtrl', function ($scope, $http, $location) {
  var url = "backend/interface/query.php"; // The url of our search
  $scope.persons = null;
  // The function that will be executed on button click (ng-click="search()")

  $scope.search = function () {
    if ($scope.query.length == 0) {
      $location.search("query", null);
    } else {
      $location.search("query", $scope.query);
    }

    if (String.toLowerCase($scope.query).indexOf("fhs") > -1 && $scope.query.length < 6) {
      $scope.persons = null;
      return;
    } else if ($scope.query.length < 4) {
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

  console.log("asdf");

  if($location.search()){
    console.log($location.search());
    var query = $location.search().query || "";
    $scope.query = query;
    $scope.search();
  }
});

App.config(['$routeProvider',
  function ($routeProvider) {
    $routeProvider.when('*', {reloadOnSearch: false, controller: "SearchCtrl"});
  }
]);