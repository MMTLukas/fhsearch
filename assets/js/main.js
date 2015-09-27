var App = angular.module("fhs-search", ["ngRoute", "ui.bootstrap"]);

angular.module('fhs-search').controller('SearchCtrl', function ($scope, $http, $location, $log) {
  var url = "backend/interface/query.php";
  $scope.persons = null;
  $scope.itemsPerPage = 8;
  $scope.needPagination = false;
  $scope.foundResults = true;
  document.querySelector(".form-wrapper input").focus();

  /**
   * Get people and details from the server
   */
  var requestPeople = function (offset) {
    $http.post(url, {"data": $scope.query, "offset": offset}).
      success(function (data) {
        if(data.count > 0){
          $scope.foundResults = true;
        }
        else{
          $scope.foundResults = false;
        }

        $scope.persons = data.people;
        $scope.totalItems = data.count;
        $scope.offset = data.offset;
        $scope.currentPage = Math.floor(data.offset / $scope.itemsPerPage)+1;

        if($scope.totalItems > $scope.itemsPerPage){
          $scope.needPagination = true;
        }else{
          $scope.needPagination = false;
        }
      })
      .error(function (data, status) {
        $scope.data = data || "Request failed";
        $scope.status = status;
        console.log("error", status, data);
      });
  };

  /**
   * Handle search input
   */
  $scope.getPeople = function (offset) {
    if ($scope.query.length == 0) {
      $location.search("q", "");
    } else {
      $location.search("q", $scope.query);
    }

    if($scope.query.toLowerCase().indexOf("fhs") > -1 && $scope.query.length >= 5
      || $scope.query.length >= 3){
      $scope.isQueryWithResults = true;
      requestPeople(0);
    }
    else{
      if($scope.query.length === 0){
        $scope.isQueryWithResults = true;
      }
      else{
        $scope.isQueryWithResults = false;
      }

      $scope.persons = null;
      $scope.needPagination = false;
      return;
    }

  };

  /**
   * Updating URL in browser
   */
  if ($location.search()) {
    var query = $location.search().q || "";
    $scope.query = query;
    $scope.getPeople();
  }

  /**
   * Pagination
   */
  $scope.pageChanged = function () {
    $log.log('Page changed to: ' + $scope.currentPage);
    requestPeople($scope.itemsPerPage*$scope.currentPage-$scope.itemsPerPage)
  };
});

App.config(['$routeProvider',
  function ($routeProvider) {
    $routeProvider.when('*', {reloadOnSearch: false, controller: "SearchCtrl"});
  }
]);
