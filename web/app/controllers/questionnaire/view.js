'use strict';

angular.module('formBuilder')
.controller('QuestionaireController', [
  '$scope', 'Questions',
  function($scope, Questions) {
    $scope.rows = [];
    $scope.isLoading = true;
    $scope.sortType = 'id';
    $scope.sortReverse = false;
    $scope.search = '';
    $scope.data = {
      totalItems: 0,
      currentPage: 1,
      maxSize: 10,
      itemsPerPage: 20
    };

    $scope.fetch = function() {
      $scope.isLoading = true;

      Questions.getTableView(
        $scope.data.currentPage,
        $scope.data.itemsPerPage,
        $scope.sortType,
        $scope.sortReverse === true ? 'desc' : 'asc',
        $scope.search
      ).then(function(response) {
        if (response.status === 'success') {
          $scope.rows = response.data.rows;
          $scope.data.totalItems = response.data.count;
        } else {
          $scope.rows = [];
        }
      }).finally(function() {
        $scope.isLoading = false;
      });
    }

    $scope.sort = function(type) {
      $scope.sortReverse = $scope.sortType === type ? !$scope.sortReverse : false;
      $scope.sortType = type;
      $scope.data.currentPage = 1;
      $scope.data.totalItems = 0;
      $scope.fetch();
    };

    $scope.searchEntry = function(keyEvent) {
      if (keyEvent.which === 13) {
        $scope.data.currentPage = 1;
        $scope.data.totalItems = 0;
        $scope.fetch();
      }
    };

    $scope.fetch();
}]);
