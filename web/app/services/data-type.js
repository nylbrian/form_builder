'use strict';

angular.module('formBuilder')
.service('DataType', function() {
  return {
    get: function() {
      return [
        {id: 1, name: "String", value: 1},
        {id: 2, name: "Number", value: 2},
        {id: 3, name: "Array", value: 3},
      ];
    }
  }
});
