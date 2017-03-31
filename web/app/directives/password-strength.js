'use strict';

angular.module('formBuilder')
.directive('passwordStrength', [
  'Password', 'TEMPLATE_URL',
  function(Password, TEMPLATE_URL) {
  return {
    restrict: 'E',
    scope: {
      password: '=',
    },
    templateUrl: TEMPLATE_URL + 'services/password-strength.html',
    link: function($scope) {
      $scope.isPasswordWeak = function() {
        return Password.getStrength($scope.password) < 40;
      };

      $scope.isPasswordStrong = function() {
        return Password.getStrength($scope.password) > 70;
      };

      $scope.isPasswordOk = function() {
        var strength = Password.getStrength($scope.password);

        return strength >= 40 && strength <= 70;
      };

      $scope.getStrength = function() {
        return Password.getStrength($scope.password);
      };

      $scope.getProgressBarType = function() {
        if ($scope.isPasswordWeak()) {
          return 'danger';
        } else if ($scope.isPasswordStrong()) {
          return 'success';
        } else if ($scope.isPasswordOk()) {
          return 'warning';
        }

        return null;
      };
    }
  };
}]);
