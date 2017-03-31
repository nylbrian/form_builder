'use strict';

angular.module('formBuilder')
.directive('optionsGrid', ['TEMPLATE_URL', 'uiGridConstants', 'Languages', 'OptionsGrid',
  function(TEMPLATE_URL, uiGridConstants, Languages, OptionsGrid) {
  return {
    restrict: 'E',
    scope: {
      item: '=',
      editable: '='
    },
    templateUrl: TEMPLATE_URL + 'options/grid.html',
    link: function($scope) {
      var options = new OptionsGrid();
      $scope.data = $scope.item.properties.data;

      $scope.addOption = function() {
        $scope.item.addData();
      };

      $scope.deleteSelected = function() {
        if (!$scope.gridApi) {
          return;
        }

        angular.forEach($scope.gridApi.selection.getSelectedRows(), function (data, index) {
          $scope.gridOptions.data.splice($scope.gridOptions.data.lastIndexOf(data), 1);
        });
      }

      $scope.$watch('item.properties.languages', function(newValue) {
        options.setLanguages($scope.item.properties.languages);
      });

      $scope.$watch('item.$$hashKey', function(newValue) {
        $scope.gridOptions.data = $scope.item.properties.data;
      });

      $scope.gridOptions = {
        enableColumnMenus: true,
        enableRowSelection: true,
        enableRowHeaderSelection: false,
        multiSelect: true,
        modifierKeysToMultiSelect: true,
        noUnselect: false,
        columnDefs: options.columns,
        data: $scope.data,
        onRegisterApi: function(gridApi) {
          $scope.gridApi = gridApi;
        },
        selectionRowHeaderWidth: 35,
      };

      if ($scope.editable) {
        $scope.gridOptions.enableCellEditOnFocus = true;
        $scope.gridOptions.showSelectionCheckbox = true;
      }
    }
  };
}]);
