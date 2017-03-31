'use strict';

angular.module('formBuilder')
.directive('optionsOverview', [
  'Languages', 'TEMPLATE_URL',
  function(Languages, TEMPLATE_URL) {
  return {
    restrict: 'E',
    scope: {
      item: '='
    },
    templateUrl: TEMPLATE_URL + 'options/overview.html',
    link: function($scope) {
      $scope.languages = [];

      Languages.get().then(function(data) {
        $scope.languages = data;
      });
    }
  };
}]);
