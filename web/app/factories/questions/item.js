'use strict';

angular.module('formBuilder')
.factory('QuestionsItem', [
  'QuestionsAnswer', 'QuestionsRouteList',
  function(QuestionsAnswer, QuestionsRouteList) {
  return function() {
    var self = this;
    var counter = 0;
    var properties = {
      questionId: '',
      name: '',
      questions: {},
      countId: null,
      display: {
        hasQuestions: false,
        hasLabels: false,
        enabled: true,
        optional: false,
      },
      labels: {},
      answers: [],
      route: [],
      id: null,
    };

    var questionsItem = {
      properties: properties,
      getItem: function() {
        return questionsItem;
      },
      getParent: function() {
        return properties.parent;
      },
      getQuestions: function() {
        return properties.questions;
      },
      getLabels: function() {
        return properties.labels;
      },
      addAnswer: function(data) {
        var answer = new QuestionsAnswer(data);
        answer.setCountId(counter);
        properties.answers.push(answer);
        counter++;
      },
      addRoute: function(data) {
        properties.route.push(new QuestionsRouteList(data));
      },
      removeRoute: function(index) {
        properties.route.splice(index, 1);
      },
      removeAnswer: function(index) {
        properties.answers.splice(index, 1);
      },
      getAnswers: function() {
        return properties.answers;
      },
      getAttributes: function() {
        return properties.attributes;
      },
      getCountId: function() {
        return properties.countId;
      },
      setCountId: function(countId) {
        properties.countId = countId;
      },
      setQuestionId: function(questionId) {
        properties.questionId = questionId;
      },
      setName: function(name) {
        properties.name = name;
      },
      setQuestions: function(questions) {
        properties.questions = questions;
      },
      setDisplay: function(display) {
        properties.display = display;
      },
      setLabels: function(labels) {
        properties.labels = labels;
      },
      setId: function(id) {
        properties.id = id;
      },
      setAnswers: function(answers) {
        counter = 0;
        angular.forEach(answers, function(value, key) {
          var answer = new QuestionsAnswer();
          answer.setCountId(counter);
          answer.setProperties(value.properties);
          properties.answers.push(answer);
          counter++;
        });
      },
      setRoute: function(routes) {
        angular.forEach(routes, function(value, key) {
          var route = new QuestionsRouteList();
          route.setProperties(value.properties);
          properties.route.push(route);
        });
      },
      setProperties: function(properties) {
        questionsItem.setCountId(properties.countId);
        questionsItem.setName(properties.name);
        questionsItem.setQuestionId(properties.questionId);
        questionsItem.setQuestions(properties.questions);
        questionsItem.setDisplay(properties.display);
        questionsItem.setLabels(properties.labels);
        questionsItem.setId(properties.id);
        questionsItem.setAnswers(properties.answers);
        questionsItem.setRoute(properties.route);
      }
    };

    return questionsItem;
  };
}]);
