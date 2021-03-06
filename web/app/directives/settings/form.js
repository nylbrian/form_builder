'use strict';

angular.module('formBuilder')
.directive('settingsForm', [
  'TEMPLATE_URL', 'Roles',
  function(TEMPLATE_URL, Roles) {
  return {
    restrict: 'E',
    scope: {
      item: '='
    },
    templateUrl: TEMPLATE_URL + 'settings/form.html',
    link: function($scope) {
      Roles.get().then(function(response) {
        $scope.roles = response;
      }, function() {
        $scope.roles = [];
      });
    }
  };
}]);
