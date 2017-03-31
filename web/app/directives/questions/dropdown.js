'use strict';

angular.module('formBuilder')
.directive('questionsDropdown', [
  '$modal', 'TEMPLATE_URL', 'MODAL_TEMPLATE_URL', 'OptionsGrid',
  function($modal, TEMPLATE_URL, MODAL_TEMPLATE_URL, OptionsGrid) {
  return {
    restrict: 'E',
    scope: {
      item: '=',
      languages: '='
    },
    templateUrl: TEMPLATE_URL + 'questions/dropdown.html',
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
              return $scope.item.value;
            }
          }
        });

        modalInstance.result.then(function(data) {
          $scope.item.option = data;
          $scope.item.value = data.properties.id;
        });
      };
    }
  };
}]);
