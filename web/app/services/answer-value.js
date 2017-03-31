'use strict';

angular.module('formBuilder')
.service('AnswerValue', function() {
  return {
    get: function() {
      return [
        {name: "Any", value: "any"},
        {name: "Specific", value: "specific"}
      ];
    }
  }
});
