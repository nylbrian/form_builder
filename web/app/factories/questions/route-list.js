'use strict';

angular.module('formBuilder')
.factory('QuestionsRouteList', [
  'QuestionsRoute', 'QuestionsRouteClear',
  function(QuestionsRoute, QuestionsRouteClear) {
  return function(data) {
    var properties = {
      list: [],
      nextQuestion: '',
      clear: []
    }

    return {
      properties: properties,
      getNextQuestion: function() {
        return properties.nextQuestion;
      },
      setNextQuestion: function(nextQuestion) {
        properties.nextQuestion = nextQuestion;
      },
      addCondition: function(data) {
        properties.list.push(new QuestionsRoute(data));
      },
      addClear: function(data) {
        properties.clear.push(new QuestionsRouteClear(data));
      },
      removeCondition: function(index) {
        properties.list.splice(index, 1);
      },
      removeClear: function(index) {
        properties.clear.splice(index, 1);
      },
      setProperties: function(data) {
        properties.nextQuestion = data.nextQuestion;

        angular.forEach(data.list, function(value, key) {
          var route = new QuestionsRoute();
          route.setProperties(value.properties);
          properties.list.push(route);
        });

        angular.forEach(data.clear, function(value, key) {
          var clear = new QuestionsRouteClear();
          clear.setProperties(value.properties);
          properties.clear.push(clear);
        });
      }
    };
  };
}]);
