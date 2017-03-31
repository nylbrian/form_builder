'use strict';

angular.module('formBuilder')
.service('Questions', [
  '$httpParamSerializerJQLike', '$resource', 'API_URL',
  function($httpParamSerializerJQLike,$resource, API_URL) {

  var request = $resource(
    API_URL, {
      controller: 'questions',
      action: '@action',
      id: '@id',
      paramOne: '@paramOne',
      paramTwo: '@paramTwo',
      paramThree: '@paramThree',
      paramFour: '@paramFour',
    }, {
      getList: {
        method: 'GET'
      },
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
      export: {
        method: 'GET',
        params: {
          action: 'export',
          id: '@id'
        }
      }
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
        return response;
      });
    },
    save: function(data) {
      return request.save(data).$promise.then(function(response) {
        return response;
      });
    },
    export: function(id, language, preview) {
      return request.export({
        id: id,
        paramOne: language,
        paramTwo: preview
      }).$promise.then(function(response) {
        return response;
      });
    }
  }
}]);
