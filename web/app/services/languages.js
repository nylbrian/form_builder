'use strict';

angular.module('formBuilder')
.service('Languages', [
  '$resource', 'API_URL',
  function($resource, API_URL) {

  var request = $resource(
    API_URL,
    {controller: 'languages'}, {
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
