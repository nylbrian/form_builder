'use strict';

angular.module('formBuilder')
.directive('questionsOverview', [
  '$modal', 'Languages', 'TEMPLATE_URL', 'MODAL_TEMPLATE_URL',
  function($modal, Languages, TEMPLATE_URL, MODAL_TEMPLATE_URL) {
  return {
    restrict: 'E',
    scope: {
      form: '=',
      selectedLanguages: '='
    },
    templateUrl: TEMPLATE_URL + 'questions/overview.html',
    link: function($scope) {
      $scope.languages = {
        all: []
      };

      Languages.get().then(function(data) {
        $scope.languages.all = data;
      });
    }
  };
}]);
