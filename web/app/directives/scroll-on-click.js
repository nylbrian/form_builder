'use strict';

angular.module('formBuilder')
.directive('scrollOnClick', function() {
  return {
    restrict: 'A',
    link: function($scope, $elem) {
      $elem.on('click', function() {
        $('body').animate({scrollTop: 0}, 'slow');
      });
    }
  }
});
