'use strict';

angular.module('formBuilder')
.directive('questionsDropdownDependency', [
  '$modal', 'TEMPLATE_URL', 'MODAL_TEMPLATE_URL', 'OptionsGrid',
  function($modal, TEMPLATE_URL, MODAL_TEMPLATE_URL, OptionsGrid) {
  return {
    restrict: 'E',
    scope: {
      item: '=',
      languages: '=',
      list: '=',
      question: '=',
    },
    templateUrl: TEMPLATE_URL + 'questions/dropdown-dependency.html',
    link: function($scope) {
      var optionsGrid;

      $scope.showOptionManager = function() {
        var modalInstance = $modal.open({
          animation: true,
          templateUrl: MODAL_TEMPLATE_URL + 'option-list.html',
          controller: 'ModalOptionListController',
          size: 'lg',
          backdrop: 'static',
          keyboard: false,
          resolve: {
            selected: function() {
              return $scope.item.properties.answer;
            }
          }
        });

        modalInstance.result.then(function(data) {
          $scope.item.properties.option = data;
          $scope.item.properties.answer = data.properties.id;
        });
      };
    }
  };
}]);
