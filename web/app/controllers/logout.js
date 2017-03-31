'use strict';

angular.module('formBuilder')
.controller('LogoutController', [
  '$scope', '$location', 'Users', 'Session',
  function($scope, $location, Users, Session) {

  Users.logout().then(function() {
    Session.destroy();
    $location.path('/login');
  });
}]);
