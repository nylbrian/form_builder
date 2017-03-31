'use strict';

angular.module('formBuilder')
.service('Input', [
  '$resource', 'API_URL',
  function($resource, API_URL) {

  var request = $resource(
    API_URL,
    {controller: 'inputTypes'}, {
      get: {
        method: 'GET'
      }
    }
  );

  return {
    get: function() {
      return request.get().
        $promise.then(function(response) {
        return response.data;
      });
    }
  }
}]);
