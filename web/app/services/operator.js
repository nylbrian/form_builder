'use strict';

angular.module('formBuilder')
.service('Operator', function() {
  return {
    getAll: function() {
      return [
        {name: "Greater Than", value: 1},
        {name: "Less Than", value: 2},
        {name: "Equal To", value: 3},
        {name: "Not Equal To", value: 4},
        {name: "Greater Than or Equal To", value: 5},
        {name: "Less Than or Equal To", value: 6},
        {name: "And", value: 7},
        {name: "Or", value: 8},
        {name: "Has", value: 9},
        {name: "Does Not Have", value: 10},
        {name: "Exists In", value: 11},
        {name: "Does Not Exist In", value: 12},
      ];
    },
    getBooleanOperator: function() {
      return [
        {name: "And", value: 7},
        {name: "Or", value: 8},
      ];
    }
  }
});
