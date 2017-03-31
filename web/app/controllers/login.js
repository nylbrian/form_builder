'use strict';

angular.module('formBuilder')
.controller('LoginController', [
  '$scope', '$location', 'Users', 'Session',
  function($scope, $location, Users, Session) {

  if (Session.isLoggedIn() === true) {
    $location.path('/home');
    return;
  }

  $scope.isLoading = false;
  $scope.errorMessage = '';

  $scope.loginUser = function() {
    $scope.isLoading = true;
    $scope.errorMessage = '';

    Users.login(
      $scope.username,
      $scope.password,
      $scope.remember
    ).then(function(response) {
      if (response.status === 'success') {
        Session.create(
          response.data.userId,
          response.data.userName,
          response.data.userRole,
          response.data.realName
        );

        $location.path('/home');
      } else {
        $scope.errorMessage = response.error;
      }
    }, function() {
      $scope.errorMessage = 'An unexpected error has occured';
    }).finally(function() {
      $scope.isLoading = false;
    });
  };

}]);
