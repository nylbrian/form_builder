'use strict';

angular.module('formBuilder')
.controller('HomeController', [
  '$scope', 'Session',
  function($scope, Session) {
    $scope.user = Session.getUser();
}]);
