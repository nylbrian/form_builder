'use strict';

angular.module('formBuilder')
.factory('QuestionsRoute', [function() {
  return function(data) {
    var properties = {
      condition: '',
      value: '',
      operator: '',
      conditionValue: '',
      question: '',
    };

    var questionsRoute = {
      properties: properties,
      setCondition: function(condition) {
        properties.condition = condition;
      },
      setValue: function(value) {
        properties.value = value;
      },
      setOperator: function(operator) {
        properties.operator = operator;
      },
      setConditionValue: function(conditionValue) {
        properties.conditionValue = conditionValue;
      },
      setQuestion: function(question) {
        properties.question = question;
      },
      setProperties: function(property) {
        questionsRoute.setCondition(property.condition);
        questionsRoute.setValue(property.value);
        questionsRoute.setOperator(property.operator);
        questionsRoute.setConditionValue(property.conditionValue);
        questionsRoute.setQuestion(property.question);
      }
    };

    return questionsRoute;
  };
}]);
