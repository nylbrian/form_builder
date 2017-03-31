'use strict';

angular.module('formBuilder')
.directive('navigation', ['TEMPLATE_URL', function(TEMPLATE_URL) {
  return {
    restrict: 'E',
    templateUrl: TEMPLATE_URL + 'navigation/main.html',
    link: function($scope) {
      $scope.isCollapsed = true;

      $scope.$on('$routeChangeSuccess', function () {
        $scope.isCollapsed = true;
      });
    }
  }
}]);
