'use strict';

angular.module('formBuilder')
.directive('questionDependency', [
  'TEMPLATE_URL', 'QuestionReference',
  function(TEMPLATE_URL, QuestionReference) {
  return {
    restrict: 'E',
    scope: {
      item: '=',
      languages: '=',
      list: '=',
      question: '=',
    },
    templateUrl: TEMPLATE_URL + 'questions/dependency.html',
    link: function($scope) {
      $scope.availableAnswers = QuestionReference.availableAnswers;
      $scope.getAnswerValues = QuestionReference.getAnswerValues;
      $scope.getAnswerType = QuestionReference.getAnswerType;
    }
  };
}]);
