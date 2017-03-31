'use strict';

angular.module('formBuilder')
.directive('questionGeneral', [
  'TEMPLATE_URL', 'AnswerType', 'StringHelper', 'QuestionReference', 'mentioUtil',
  function(TEMPLATE_URL, AnswerType, StringHelper, QuestionReference, mentioUtil) {
  return {
    restrict: 'E',
    scope: {
      item: '=',
      languages: '=',
      list: '=',
    },
    templateUrl: TEMPLATE_URL + 'questions/general.html',
    link: function($scope) {
      $scope.answers = [];

      $scope.isFormDisabled = function() {
        return $scope.noSelectedLanguages() ||
          $scope.noSelectedQuestion();
      };

      $scope.noSelectedLanguages = function() {
        return $scope.languages.length <= 0;
      };

      $scope.noSelectedQuestion = function() {
        return Object.keys($scope.item).length <= 0;
      };

      $scope.searchAnswers = function(term) {
        var mentionAnswers = [{name: 'abc', label: 'aa'}, {name: '123abc', label: '123aa'}];
        $scope.mentionAnswers = QuestionReference.getAllAnswersForMention($scope.list, $scope.item.properties.countId);
        return mentionAnswers;
      };

      $scope.getAnswerText = function(item) {
        return '[[' + item.name + ']]';
      };

      $scope.removeSpaces = StringHelper.removeSpaces;

      $scope.answerType = AnswerType.get();
    }
  };
}]);
