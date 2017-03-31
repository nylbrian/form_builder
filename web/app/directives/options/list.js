'use strict';

angular.module('formBuilder')
.directive('optionsList', ['TEMPLATE_URL',
  function(TEMPLATE_URL) {
  return {
    restrict: 'E',
    scope: {
      list: '=',
      languages: '='
    },
    templateUrl: TEMPLATE_URL + 'options/list.html',
    link: function($scope) {
      $scope.isSelected = $scope.list.isSelected;
      $scope.setSelected = $scope.list.setSelected;
      $scope.addItem = $scope.list.addItem;
      $scope.removeSelected = $scope.list.removeSelected;
    }
  };
}]);
