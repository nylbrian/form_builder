'use strict';

angular.module('formBuilder')
.directive('questionRoute', [
  '$modal', 'TEMPLATE_URL', 'Input', 'Operator', 'QuestionReference', 'AnswerValue', 'MODAL_TEMPLATE_URL',
  function($modal, TEMPLATE_URL, Input, Operator, QuestionReference, AnswerValue, MODAL_TEMPLATE_URL) {
  return {
    restrict: 'E',
    scope: {
      item: '=',
      languages: '=',
      list: '='
    },
    templateUrl: TEMPLATE_URL + 'questions/route.html',
    link: function($scope) {
      $scope.availableAnswers = function(count) {
        if ($scope.list.length <= 0) {
          return;
        }

        for (var i = 0; i < $scope.list.length; i++) {
          if ($scope.list[i].getCountId() === count) {
            return $scope.list[i].properties.answers;
          }
        }

        return;
      };

      $scope.isDropdown = function(countId, value) {
        var ids = [2, 3, 6];

        return ids.indexOf($scope.getAnswerType($scope.list, countId, value)) === -1;
      }

      $scope.formConditions = Operator.getAll();
      $scope.formOperators = Operator.getBooleanOperator();
      $scope.formValues = AnswerValue.get();
      $scope.getAnswerValues = QuestionReference.getAnswerValues;
      $scope.getAnswerType = QuestionReference.getAnswerType;
    }
  };
}]);
