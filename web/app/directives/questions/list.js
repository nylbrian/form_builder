'use strict';

angular.module('formBuilder')
.directive('questionsList', [
  'TEMPLATE_URL',
  function(TEMPLATE_URL) {
  return {
    restrict: 'E',
    scope: {
      questionForm: '=',
      questions: '=',
      languages: '=',
      questionValidator: '='
    },
    templateUrl: TEMPLATE_URL + 'questions/list.html',
    link: function($scope) {
      $scope.newItem = function() {
        $scope.questions.addItem($scope.questions.list);
      };

      $scope.newSubItem = function(scope) {
        $scope.questions.addItem(scope.$modelValue.children);
      };

      $scope.removeRow = function(scope) {
        scope.remove();
      };

      $scope.copyItem = function() {
        if ($scope.questions.getSelected()) {
          $scope.questions.copyItem(
            $scope.questions.getSelected(),
            $scope.questions.list
          );
        }
      };

      $scope.toggleRow = function(scope) {
        scope.toggle();
      };

      $scope.hasError = function(countId) {
        if (!angular.isDefined($scope.questionValidator.questions['question' + countId])) {
          return false;
        }

        var element = $scope.questionValidator.questions['question' + countId];
        if (element.$dirty && element.$invalid) {
          return true;
        }
      };

      $scope.isSelected = $scope.questions.isSelectedItem;
      $scope.list = $scope.questions.list;
      $scope.treeOptions = {
        beforeDrag: function(sourceNodeScope) {
          $scope.questions.setSelected(sourceNodeScope.$modelValue);
          $scope.$applyAsync();
          return true;
        }
      };

      if ($scope.questionForm.mode === 'add') {
        $scope.newItem();
      }
    }
  };
}]);
