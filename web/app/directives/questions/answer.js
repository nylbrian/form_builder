'use strict';

angular.module('formBuilder')
.directive('questionAnswer', [
  'TEMPLATE_URL', 'Input', 'DataType', 'StringHelper', 'Operator', 'QuestionReference', 'mentioUtil',
  function(TEMPLATE_URL, Input, DataType, StringHelper, Operator, QuestionReference, mentioUtil) {
  return {
    restrict: 'E',
    scope: {
      item: '=',
      languages: '=',
      list: '=',
      question: '=',
      index: '=',
      questions: '=',
    },
    templateUrl: TEMPLATE_URL + 'questions/answer.html',
    link: function($scope) {
      $scope.dataTypes = DataType.get();
      $scope.operators = Operator.getAll();
      $scope.inputTypes = [];
      $scope.answers = [];

      $scope.searchAnswers = function(term) {
        var mentionAnswers = [];
        $scope.mentionAnswers = QuestionReference.getAllAnswersForMention($scope.list, $scope.item.properties.countId);
        return mentionAnswers;
      };

      $scope.searchAnswersFormula = function(term, answerCount) {
        var mentionAnswers = [];
        $scope.mentionAnswers = QuestionReference.getAllAnswerMentionFormula(
          $scope.list,
          $scope.item.properties.countId,
          answerCount
        );
        return mentionAnswers;
      };

      $scope.getAnswerText = function(item) {
        return '[[' + item.name + ']]';
      };

      $scope.removeSpaces = StringHelper.removeSpaces;

      Input.get().then(function(data) {
        $scope.inputTypes = data;
      });

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

      $scope.getAnswerValues = QuestionReference.getAnswerValues;

      $scope.isDropdown = function(question, answer) {
        var ids = [2, 3, 6];

        return ids.indexOf(QuestionReference.getAnswerType($scope.list, question, answer)) === -1;
      }
    }
  };
}]);
