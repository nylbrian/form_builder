'use strict';

angular.module('formBuilder')
.directive('optionsForm', ['TEMPLATE_URL', 'Languages', 'OptionsGrid',
  function(TEMPLATE_URL, Languages, OptionsGrid) {
  return {
    restrict: 'E',
    scope: {
      item: '='
    },
    templateUrl: TEMPLATE_URL + 'options/form.html',
    link: function($scope) {
      $scope.isFormDisabled = function() {
        return Object.keys($scope.item).length <= 0;
      };

      $scope.addOption = function() {
        $scope.item.properties.data.push({});
      };

      $scope.languages = [];
      Languages.get().then(function(data) {
        $scope.languages = data;
      });
    }
  };
}]);
