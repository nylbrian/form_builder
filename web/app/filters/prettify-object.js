'use strict';

angular.module('formBuilder')
.filter('prettifyObject', function() {
  return function(obj) {
    return angular.toJson(obj, true);
  };
});
