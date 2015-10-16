var app = angular.module("fhs-search", ["ui.router", "ui.bootstrap"]);

angular.module('fhs-search').controller('SearchCtrl', function ($scope, $http, $location, $log) {
    var url = "backend/interface/query.php";
    $scope.persons = null;
    $scope.itemsPerPage = 8;
    $scope.needPagination = false;
    $scope.foundResults = true;
    document.querySelector("input").focus();

    /**
     * Get people and details from the server
     */
    var requestPeople = function () {

        $http.post(url, {"data": $scope.query, "offset": $scope.offset})
            .success(function (data) {
                $scope.persons = data.people;
                $scope.totalItems = data.count;
                $scope.offset = data.offset;
                $scope.currentPage = Math.floor(data.offset / $scope.itemsPerPage) + 1;
                $scope.needPagination = $scope.totalItems > $scope.itemsPerPage ? true : false;
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
    $scope.getPeople = function () {
        $location.search("q", $scope.query);

        // Reset offset and query
        if (!$scope.query) {
            $scope.offset = 0;
        }

        $location.search("offset", $scope.offset)

        if ($scope.query.toLowerCase().indexOf("fhs") > -1 && $scope.query.length >= 5 || $scope.query.length >= 3) {
            requestPeople($scope.offset);
        }
        else {
            $scope.persons = null;
            $scope.needPagination = false;
        }
    };

    /**
     * Updating URL in browser
     * @example: User comes with http://host/#?q=lukas&offset=40 to the site
     *           now we extract the query and the offset and search for the peoples
     */
    if ($location.search()) {
        $scope.query = $location.search().q || "";
        $scope.offset = $location.search().offset || 0;
        $scope.getPeople();
    }

    /**
     * Pagination
     */
    $scope.pageChanged = function () {
        $scope.offset = $scope.itemsPerPage * $scope.currentPage - $scope.itemsPerPage;

        $location.search("offset", $scope.offset);
        $scope.getPeople();
    };
});

app.config(function ($stateProvider) {
    $stateProvider.state('*', {
        reloadOnSearch: false,
        controller: "SearchCtrl"
    });
});

