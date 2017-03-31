'use strict';

angular.module('formBuilder')
.filter('filterQuestionList', function() {
  return function(list, active) {
    return list.filter(function(item) {
      return item.properties.countId != active.properties.countId;
    });
  };
});
