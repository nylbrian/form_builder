'use strict';

angular.module('formBuilder')
.controller('ExportQuestionController', [
  '$scope', '$window', 'Questions', 'Languages',
  function($scope, $window, Questions, Languages) {
  $scope.values = {
    language: null,
    question: null,
    preview: false,
    previewObject: {},
  }

  Questions.getTableView(1, 100000).then(function(response) {
    $scope.questions = response.data.rows;
  });

  Languages.get().then(function(data) {
    $scope.languages = data;
  });

  $scope.submit = function() {
    if (!$scope.values.preview) {
      $window.location.href = '../rest/index.php?request=questions/export/' + $scope.values.question.id + '/' + $scope.values.language.id;
      return;
    }

    Questions.export(
      $scope.values.question.id,
      $scope.values.language.id,
      $scope.values.preview
    ).then(function(response) {
      $scope.values.previewObject = response.data;
    });
  };

}]);
