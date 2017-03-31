'use strict';

angular.module('formBuilder')
.controller('EditUsersController', [
  '$scope', '$routeParams', '$location', 'Objects', 'UsersItem', 'Users',
  function($scope, $routeParams, $location, Objects, UsersItem, Users) {
  $scope.item = new UsersItem();
  $scope.item.setMode('edit');

  $scope.errorMessage = '';
  $scope.formDisabled = false;
  $scope.isLoading = true;
  $scope.isValid = false;

  Users.get($routeParams.userId).then(function(response) {
    if (response !== false) {
      $scope.item.setFromObject(response);
      $scope.isValid = true;
    }
  }).catch(function() {

  }).finally(function() {
    $scope.isLoading = false;
  });

  $scope.isFormDisabled = function() {
    return $scope.formDisabled;
  };

  $scope.save = function(form) {
    if (form.$valid === false) {
      Objects.setDirty(form.$error.required);
      return;
    }

    $scope.errorMessage = '';
    $scope.formDisabled = true;

    var data = Objects.removeFunction($scope.item.properties);

    Users.save(data).then(function(response) {
      $scope.formDisabled = false;
      if (response.status === 'success') {
        $location.path('/users');
      } else {
        $scope.errorMessage = response.error;
      }
    }).finally(function() {
      $scope.formDisabled = false;
    });
  };
}]);
