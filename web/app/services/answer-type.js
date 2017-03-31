'use strict';

angular.module('formBuilder')
.service('AnswerType', function() {
  return {
    get: function() {
      return [
        {id: 1, name: "Single", value: "single"},
        {id: 2, name: "Multiple", value: "Multiple"},
        {id: 3, name: "With Dependency", value: "dependency"},
      ];
    }
  }
});
