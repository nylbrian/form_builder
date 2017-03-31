'use strict';

angular.module('formBuilder')
.controller('ModalOptionListController', [
  '$scope', '$modalInstance', 'Options', 'OptionsList', 'selected',
  function($scope, $modalInstance, Options, OptionsList, selected) {

  $scope.select = function () {
    if ($scope.canSelect()) {
      $modalInstance.close($scope.list.getSelected());
    }
  };

  $scope.save = function() {
    var selected = $scope.list.getSelected();

    Options.save(selected.properties).then(function(response) {
      selected.setId(response.id);
      selected.fetch();
      $scope.select();
    });
  };

  $scope.cancel = function () {
    $modalInstance.dismiss('canceled');
  };

  $scope.canSave = function() {
    var selected = $scope.list.getSelected();

    if (Object.keys(selected).length > 0) {
      return true;
    }

    return false;
  }

  $scope.canSelect = function() {
    var selected = $scope.list.getSelected();

    if (Object.keys(selected).length > 0 && selected.properties.id) {
      return true;
    }

    return false;
  }

  $scope.list = new OptionsList(selected);
}]);
