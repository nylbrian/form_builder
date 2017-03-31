'use strict';

angular.module('formBuilder')
.factory('QuestionsRouteClear', [
  function() {
  return function(data) {
    var properties = {
      question: '',
      answer: '',
    }

    var questionsRouteClear = {
      properties: properties,
      setQuestion: function(question) {
        properties.question = question;
      },
      setAnswer: function(answer) {
        properties.answer = answer;
      },
      setProperties: function(property) {
        questionsRouteClear.setQuestion(property.question);
        questionsRouteClear.setAnswer(property.answer);
      }
    };

    return questionsRouteClear;
  };
}]);
