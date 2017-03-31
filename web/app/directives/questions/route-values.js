'use strict';

angular.module('formBuilder')
.directive('questionRouteValues', [
  '$modal', 'TEMPLATE_URL', 'QuestionReference', 'MODAL_TEMPLATE_URL',
  function($modal, TEMPLATE_URL, QuestionReference, MODAL_TEMPLATE_URL) {
  return {
    restrict: 'E',
    scope: {
      item: '=',
      question: '=',
      answer: '=',
      list: '=',
    },
    templateUrl: TEMPLATE_URL + 'questions/route-values.html',
    link: function($scope) {
      $scope.isDropdown = function(question, answer) {
        var ids = [2, 3, 6];

        return ids.indexOf(QuestionReference.getAnswerType($scope.list, question, answer)) === -1;
      }

      $scope.showRouteValues = function() {
        var modalInstance = $modal.open({
          animation: true,
          templateUrl: MODAL_TEMPLATE_URL + 'route-values.html',
          controller: 'ModalRouteValuesController',
          size: 'lg',
          backdrop: 'static',
          keyboard: false,
          resolve: {
            IsDropdown: function() {
              return $scope.isDropdown($scope.question, $scope.answer);
            },
            DropdownList: function() {
              return QuestionReference.getAnswerValues($scope.list, $scope.question, $scope.answer);
            },
            Values: function() {
              return $scope.item;
            },
          }
        });

        modalInstance.result.then(function(data) {
          $scope.item = data;
          console.log(data);
        });
      };
    }
  };
}]);
