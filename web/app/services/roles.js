'use strict';

angular.module('formBuilder')
.service('Roles', [
  '$resource', 'API_URL',
  function($resource, API_URL) {

  var request = $resource(
    API_URL,
    {controller: 'roles'}, {
      get: {
        method: 'GET'
      },
      save: {
        action: 'save',
        method: 'POST'
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
