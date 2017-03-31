'use strict';

angular.module('formBuilder')
.directive('selectRequired', function() {
  return {
    require: 'ngModel',
    link: function(scope, elm, attrs, ctrl) {
      ctrl.$validators.required = function(modelValue, viewValue) {
        var determineVal;
        if (angular.isObject(modelValue)) {
          determineVal = modelValue;
        } else if (angular.isObject(viewValue)) {
          determineVal = viewValue;
        } else {
          return false;
        }

        return Object.keys(determineVal).length > 0;
      };
    }
  };
});
