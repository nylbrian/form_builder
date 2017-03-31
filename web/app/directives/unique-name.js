'use strict';

angular.module('formBuilder')
.directive('uniqueName', [
  'QuestionReference',
  function(QuestionReference) {
  return {
    require: 'ngModel',
    link: function(scope, element, attrs, ngModel) {
      function customValidator(ngModelValue) {
        var vars = scope.$eval(attrs.uniqueName);
        var currentValue = element.val();

        if (currentValue === '') {
          return true;
        }

        var unique = QuestionReference.uniqueName(
          vars.list,
          vars.questionId,
          vars.answerId,
          element.val()
        );

        ngModel.$setValidity('unique', unique);
        return ngModelValue;
      }

      ngModel.$parsers.push(customValidator);
    }
  };
}]);
