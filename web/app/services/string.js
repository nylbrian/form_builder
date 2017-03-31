'use strict';

angular.module('formBuilder')
.service('StringHelper', [
  function() {

  var string = {
    removeSpaces: function(str) {
      if (!str || typeof str !== 'string') {
        return;
      }

      return str.replace(/ /g, '');
    }
  }

  return string;
}]);
