'use strict';

angular.module('formBuilder')
.controller('EditOptionsController', [
  '$scope', '$routeParams', '$location', 'Objects', 'Options', 'OptionsItem',
  function($scope, $routeParams, $location, Objects, Options, OptionsItem) {
  $scope.item = new OptionsItem();

  $scope.errorMessage = '';
  $scope.formDisabled = false;
  $scope.isLoading = true;
  $scope.isValid = false;

  Options.get($routeParams.optionsId).then(function(response) {
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

    var data = angular.copy($scope.questionForm);
    data = Objects.removeFunction($scope.item.properties);

    Options.save(data).then(function(response) {
      $scope.formDisabled = false;
      if (response.status === 'success') {
        $location.path('/options');
      } else {
        $scope.errorMessage = response.error;
      }
    }).finally(function() {
      $scope.formDisabled = false;
    });
  };

}]);
