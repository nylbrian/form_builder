'use strict';

angular.module('formBuilder')
.directive('questionProperties', [
  'TEMPLATE_URL', 'Input',
  function(TEMPLATE_URL, Input) {
  return {
    restrict: 'E',
    scope: {
      item: '=',
      languages: '=',
      list: '=',
      questions: '=',
    },
    templateUrl: TEMPLATE_URL + 'questions/properties.html',
    link: function($scope) {

      $scope.isFormDisabled = function() {
        return $scope.noSelectedLanguages() ||
          $scope.noSelectedQuestion();
      };

      $scope.noSelectedLanguages = function() {
        return $scope.languages.length <= 0;
      };

      $scope.noSelectedQuestion = function() {
        return Object.keys($scope.item).length <= 0;
      };

      $scope.inputTypes = [];
      Input.get().then(function(data) {
        $scope.inputTypes = data;
      });
    }
  };
}]);
