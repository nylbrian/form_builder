'use strict';

angular.module('formBuilder')
.service('Password', function() {
  return {
    getStrength: function(password) {
      var score = 0;

      if (!password) {
        return score;
      }

      var letters = new Object();

      for (var i = 0; i < password.length; i++) {
        letters[password[i]] = (letters[password[i]] || 0) + 1;
        score += 5.0 / letters[password[i]];
      }

      var variations = {
        digits: /\d/.test(password),
        lower: /[a-z]/.test(password),
        upper: /[A-Z]/.test(password),
        nonWords: /\W/.test(password),
      };

      var count = 0;

      for (var check in variations) {
        count += (variations[check] === true) ? 1 : 0;
      }

      score += (count - 1) * 10;

      if (score > 100) {
        score = 100;
      }

      return parseInt(score);
    }
  };
});
