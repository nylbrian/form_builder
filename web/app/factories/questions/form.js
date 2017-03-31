'use strict';

angular.module('formBuilder')
.factory('QuestionsForm', [
  'QuestionsList',
  function(QuestionsList) {
  return function(data) {
    var properties = {
      name: '',
      id: '',
      mode: 'add',
      description: '',
      enabled: true,
      languages: [],
      questions: new QuestionsList()
    };

    var questionList =  {
      properties: properties,
      setMode: function(newMode) {
        properties.mode = newMode;
      },
      setName: function(name) {
        properties.name = name;
      },
      setDescription: function(description) {
        properties.description = description;
      },
      setEnabled: function(enabled) {
        properties.enabled = enabled;
      },
      setLanguages: function(languages) {
        properties.languages = languages;
      },
      setQuestions: function(questions) {
        properties.questions.setList(questions.list);
      },
      setId: function(id) {
        properties.id = id;
      },
      setProperties: function(property) {
        questionList.setName(property.name);
        questionList.setDescription(property.description);
        questionList.setId(property.id);
        questionList.setEnabled(property.enabled);
        questionList.setLanguages(property.languages);
        questionList.setQuestions(property.questions);
      }
    }

    return questionList;
  };
}]);
