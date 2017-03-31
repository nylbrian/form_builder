'use strict';

angular.module('formBuilder')
.directive('questionsDropdownGrid', ['TEMPLATE_URL', 'uiGridConstants', 'Languages', 'OptionsGrid',
  function(TEMPLATE_URL, uiGridConstants, Languages, OptionsGrid) {
  return {
    restrict: 'E',
    scope: {
      item: '=',
      languages: '='
    },
    templateUrl: TEMPLATE_URL + 'questions/dropdown-grid.html',
    link: function($scope) {
      var options = new OptionsGrid();
      $scope.data = $scope.item.properties.data;

      $scope.$watch('languages.length', function() {
        options.setLanguages($scope.languages);
      });

      $scope.$watch('item.$$hashKey', function() {
        $scope.gridOptions.data = $scope.item.properties.data;
      });

      $scope.gridOptions = {
        autoResize: true,
        enableCellEditOnFocus: true,
        enableColumnMenus: true,
        enableRowSelection: true,
        enableRowHeaderSelection: false,
        multiSelect: false,
        modifierKeysToMultiSelect: false,
        noUnselect: true,
        columnDefs: options.columns,
        data: $scope.data,
        onRegisterApi: function(gridApi) {
          $scope.gridApi = gridApi;
        }
      };
    }
  };
}]);
