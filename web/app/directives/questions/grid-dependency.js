'use strict';

angular.module('formBuilder')
.directive('questionsGridDependency', [
  '$modal', 'TEMPLATE_URL', 'MODAL_TEMPLATE_URL', 'Options', 'QuestionReference',
  function($modal, TEMPLATE_URL, MODAL_TEMPLATE_URL, Options, QuestionReference) {
  return {
    restrict: 'E',
    scope: {
      item: '=',
      languages: '=',
      list: '=',
      question: '=',
    },
    templateUrl: TEMPLATE_URL + 'questions/grid-dependency.html',
    link: function($scope) {
      $scope.showOptionManager = function(grid, row) {
        var modalInstance = $modal.open({
          animation: true,
          templateUrl: MODAL_TEMPLATE_URL + 'option-list.html',
          controller: 'ModalOptionListController',
          size: 'lg',
          backdrop: 'static',
          keyboard: false,
          resolve: {
            grid: function () { return grid; },
            row: function () { return row; },
            selected: function() {
              return row.entity.answer;
            }
          }
        });

        modalInstance.result.then(function(data) {
          $scope.optionRows.unshift(data.properties);
          row.entity.answer = data.properties.id;
          row.entity.option = data;
        });
      };

      $scope.getColumnDefs = function() {

      };

      $scope.init = function() {
        $scope.source = QuestionReference.getAnswerValues(
          $scope.list,
          $scope.item.properties.dependency.question,
          $scope.item.properties.dependency.answer
        );

        $scope.gridOptions = {
          autoResize: true,
          enableCellEditOnFocus: true,
          enableColumnMenus: true,
          enableRowSelection: true,
          enableRowHeaderSelection: false,
          multiSelect: false,
          modifierKeysToMultiSelect: false,
          noUnselect: true,
          columnDefs: [
            {
              name: 'Source',
              field: 'source',
              editableCellTemplate: 'ui-grid/dropdownEditor',
              editDropdownValueLabel: 'name',
              editDropdownOptionsArray: $scope.source,
              cellFilter: 'griddropdown:grid.appScope.source:"id":"name"',
            },
            {
              name: 'Value',
              field: 'answer',
              editableCellTemplate: TEMPLATE_URL + 'questions/edit-dependency-value.html',
              cellFilter: 'gridanswer:row',
            }
          ],
          data: $scope.item.properties.dependency.list,
          onRegisterApi: function(gridApi) {
            $scope.gridApi = gridApi;
          }
        };
      };

      $scope.addOption = function() {
        $scope.item.addDependency();
      };

      $scope.deleteSelected = function() {
        if (!$scope.gridApi) {
          return;
        }

        angular.forEach($scope.gridApi.selection.getSelectedRows(), function (data, index) {
          $scope.gridOptions.data.splice($scope.gridOptions.data.lastIndexOf(data), 1);
        });
      };

      Options.getTableView(0, 100000)
      .then(function(response) {
        if (response.status === 'success') {
          $scope.optionRows = response.data.rows;
          $scope.init();
        }
      });
    }
  };
}]);
