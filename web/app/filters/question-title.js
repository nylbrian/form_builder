'use strict';

angular.module('formBuilder')
.filter('questionTitle', function() {
  return function(questionList, languages) {
    if (languages.length <= 0 ||
      !angular.isObject(questionList) || 
      Object.keys(questionList).length <= 0) {
      return '';
    }
    return questionList[languages[0].id];
  };
});