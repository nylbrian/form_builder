'use strict';

angular.module('formBuilder')
.service('Users', [
  '$httpParamSerializerJQLike', '$resource', 'API_URL',
  function($httpParamSerializerJQLike, $resource, API_URL) {

  var request = $resource(
    API_URL,
    {
      controller: 'users',
      action: '@action',
      id: '@id',
      paramOne: '@paramOne',
      paramTwo: '@paramTwo',
      paramThree: '@paramThree',
      paramFour: '@paramFour',
    }, {
      get: {
        method: 'GET',
        params: {
          action: 'get',
          id: '@id'
        }
      },
      getTableView: {
        method: 'GET',
        params: {
          action: 'getTableView',
          id: '@id',
          paramOne: '@paramOne',
          paramTwo: '@paramTwo',
          paramThree: '@paramThree',
          paramFour: '@paramFour',
        }
      },
      save: {
        method: 'POST',
        params: {
          action: 'save'
        },
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        transformRequest: function(obj) {
          return $httpParamSerializerJQLike(obj);
        }
      },
      login: {
        method: 'POST',
        params: {
          action: 'login'
        },
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        transformRequest: function(obj) {
          return $httpParamSerializerJQLike(obj);
        }
      },
      getLoggedUser: {
        method: 'GET',
        params: {
          action: 'getLoggedUser'
        }
      },
      logout: {
        method: 'GET',
        params: {
          action: 'logout'
        }
      },
      updateSettings: {
        method: 'POST',
        params: {
          action: 'updateSettings'
        },
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        transformRequest: function(obj) {
          return $httpParamSerializerJQLike(obj);
        }
      },
    }
  );

  return {
    getTableView: function(currentPage, itemsPerPage, sortBy, sortOrder, search) {
      return request.getTableView({
        id: currentPage,
        paramOne: itemsPerPage,
        paramTwo: sortBy,
        paramThree: sortOrder,
        paramFour: search,
      }).$promise.then(function(response) {
        return response;
      });
    },
    get: function(id) {
      return request.get({id: id}).
        $promise.then(function(response) {
        return response.data;
      });
    },
    save: function(data) {
      return request.save(data).$promise.then(function(response) {
        return response;
      });
    },
    login: function(username, password, remember) {
      return request.login({
        username: username,
        password: password,
        remember: remember
      }).$promise.then(function(response) {
        return response;
      });
    },
    getLoggedUser: function() {
      return request.getLoggedUser().$promise.then(function(response) {
        return response;
      });
    },
    logout: function() {
      return request.logout().$promise.then(function(response) {
        return response;
      });
    },
    updateSettings: function(data) {
      return request.updateSettings(data).$promise.then(function(response) {
        return response;
      });
    },
  }
}]);
