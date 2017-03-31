'use strict';

angular.module('formBuilder')
.controller('SettingsController', [
  '$rootScope', '$scope', '$location', 'Users', 'Objects', 'UsersItem', 'Session', 'AUTH_EVENTS',
  function($rootScope, $scope, $location, Users, Objects, UsersItem, Session, AUTH_EVENTS) {

  var userInfo = Session.getUser();
  $scope.isLoading = false;
  $scope.item = new UsersItem();
  $scope.errorMessage = '';
  $scope.formDisabled = true;

  $scope.loadUser = function(id) {
    Users.get(id).then(function(response) {
      if (response !== false) {
        $scope.item.setFromObject(response);
      }
    }).catch(function() {

    }).finally(function() {
      $scope.isLoading = false;
      $scope.formDisabled = false;
    });
  };

  $scope.update = function(form) {
    if (form.$valid === false) {
      Objects.setDirty(form.$error.required);
      return;
    }

    $scope.errorMessage = '';
    $scope.formDisabled = true;

    var data = Objects.removeFunction($scope.item.properties);

    Users.updateSettings(data).then(function(response) {
      $scope.formDisabled = false;
      if (response.status === 'success') {
        Session.setRealName(data.name);
        $location.path('/home');
      } else {
        $scope.errorMessage = response.error;
      }
    }).finally(function() {
      $scope.formDisabled = false;
    });

  };

  $rootScope.$on(AUTH_EVENTS.loginSuccess, function() {
    userInfo = Session.getUser();
    $scope.loadUser(userInfo.userId);
  });

  if (userInfo.userId) {
    $scope.loadUser(userInfo.userId);
  }

}]);
