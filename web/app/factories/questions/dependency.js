'use strict';

angular.module('formBuilder')
.factory('QuestionsDependency', [function() {
  return function(data) {
    var properties = {
      source: '',
      answer: ''
    };

    return {
      source: properties.source,
      answer: properties.answer,
      setProperties: function(data) {
        console.log(data);
        properties.source = data.source;
        properties.answer = data.answer;

        if (angular.isDefined(data.option)) {
          properties.option = data.option;
        }
      }
    };
  };
}]);
