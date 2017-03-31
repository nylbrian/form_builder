'use strict';

angular.module('formBuilder')
.controller('CreateQuestionController', [
  '$scope', '$location', 'Objects', 'Questions', 'QuestionsForm', 'Languages',
  function($scope, $location, Objects, Questions, QuestionsForm, Languages) {
  $scope.errorMessage = '';
  $scope.formDisabled = false;

  $scope.isFormDisabled = function() {
    return $scope.formDisabled;
  };

  var questionForm = new QuestionsForm();
  questionForm.setMode('add');
  $scope.questionForm = questionForm.properties;

  $scope.save = function(form) {
    if (form.$valid === false) {
      Objects.setDirty(form.$error.required);
      return;
    }

    $scope.errorMessage = '';
    $scope.formDisabled = true;

    var data = angular.copy($scope.questionForm);
    data = Objects.removeFunction(data);
    Questions.save(data).then(function(response) {
      $scope.formDisabled = false;
      if (response.status === 'success') {
        $location.path('/questionnaire');
      } else {
        $scope.errorMessage = response.error;
      }
    }).finally(function() {
      $scope.formDisabled = false;
    });
  };

}]);
