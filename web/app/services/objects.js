'use strict';

angular.module('formBuilder')
.service('Objects', [
  function() {

  var Objects = {
    removeFunction: function(obj) {
      var data = {};

      angular.forEach(obj, function(value, key) {
          if (key !== 'option') {
            if (angular.isObject(value)) {
              data[key] = Objects.removeFunction(value);
            } else if (!angular.isFunction(value)) {
              data[key] = value;
            }
          }
      });

      return data;
    },
    setDirty: function(obj) {
      angular.forEach(obj, function(value) {
        value.$setDirty();

        if (angular.isDefined(value.$error.required)) {
          Objects.setDirty(value.$error.required);
        }
      });
    }
  }

  return Objects;
}]);
