'use strict';

angular.module('formBuilder')
.controller('ModalRouteValuesController', [
  '$scope', '$modalInstance', 'IsDropdown', 'DropdownList', 'Values',
  function($scope, $modalInstance, IsDropdown, DropdownList, Values) {

  $scope.dropdownList = DropdownList;
  $scope.isDropdown = IsDropdown;
  $scope.values = Values;
  $scope.allSelected = false;

  $scope.update = function() {
    var value = [];

    if ($scope.isDropdown === true) {
      angular.forEach($scope.dropdownList, function(item) {
        if (item.selected === true) {
          value.push(item.id);
        }
      });
    } else {

    }

    $modalInstance.close(value.join("|||"));
  };

  $scope.cancel = function () {
    $modalInstance.dismiss('canceled');
  };

  $scope.selectAll = function() {
    $scope.allSelected = !$scope.allSelected;

    angular.forEach($scope.dropdownList, function(item) {
      item.selected = $scope.allSelected;
    });
  };

  if ($scope.isDropdown === true && angular.isString($scope.values)) {
    var selected = $scope.values.split("|||");

    angular.forEach($scope.dropdownList, function(item) {
      if (selected.indexOf(item.id) !== -1) {
        item.selected = true;
      }
    });
  }
}]);
