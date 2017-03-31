'use strict';

angular.module('formBuilder')
.controller('MainController', [
  '$rootScope', '$scope', '$location', 'Session', 'AUTH_EVENTS',
  function($rootScope, $scope, $location, Session, AUTH_EVENTS) {

  $scope.isCurrentPage = function(path, parent) {
    path = '/' + path;
    if ($location.path() === path ||
      ($location.path().substr(0, path.length) === path &&
        parent === true)) {
      return true;
    }

    return false;
  };

  $rootScope.$on(AUTH_EVENTS.notAuthenticated, function() {
    $location.path('/login');
  });

  $scope.user = Session.getUser();
}]);
