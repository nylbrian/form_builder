'use strict';

angular.module('formBuilder')
.controller('CreateUsersController', [
  '$scope', '$location', 'Objects', 'UsersItem', 'Users',
  function($scope, $location, Objects, UsersItem, Users) {
  $scope.item = new UsersItem();
  $scope.errorMessage = '';
  $scope.formDisabled = false;

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
