'use strict';

angular.module('formBuilder')
.controller('EditQuestionController', [
  '$scope', '$routeParams', '$location', 'Objects', 'Questions', 'QuestionsForm', 'Languages',
  function($scope, $routeParams, $location, Objects, Questions, QuestionsForm, Languages) {

  var questionForm = new QuestionsForm();
  questionForm.setMode('edit');

  $scope.questionForm = questionForm.properties;
  $scope.errorMessage = '';
  $scope.formDisabled = false;
  $scope.isLoading = true;
  $scope.isValid = false;

  Questions.get($routeParams.questionnaireId).then(function(response) {
    if (response.data !== false) {
      questionForm.setProperties(response.data);
      $scope.isValid = true;
    }
  }).catch(function() {

  }).finally(function() {
    $scope.isLoading = false;
  });

  $scope.isFormDisabled = function() {
    return $scope.formDisabled;
  };

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
