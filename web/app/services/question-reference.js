'use strict';

angular.module('formBuilder')
.service('QuestionReference', [function() {
  var options = [];

  var QuestionReference = {
    uniqueName: function(list, questionId, answerId, name) {
      var identifier = questionId + '_' + answerId;

      if (list.length <= 0) {
        return true;
      }

      for (var i = 0; i < list.length; i++) {
        if (answerId == null && questionId !== list[i].properties.countId && list[i].properties.questionId === name) {
          return false;
        } else if (answerId !== null && list[i].properties.questionId === name) {
          return false;
        }

        if (list[i].properties.answers.length <= 0) {
          continue;
        }

        var answer = list[i].properties.answers;
        for (var j = 0; j < answer.length; j++) {
          var answerIdentifier = list[i].properties.countId + '_' + answer[j].properties.countId;
          if (answerIdentifier !== identifier && answer[j].properties.name === name) {
            return false;
          }
        }
      }

      return true;
    },
    getAllAnswersForMention: function(list, count) {
      var answers = [];

      if (list.length <= 0) {
        return answers;
      }

      for (var i = 0; i < list.length; i++) {
        if (list[i].properties.answers.length <= 0 || list[i].getCountId() === count) {
          continue;
        }

        var answer = list[i].properties.answers;
        for (var j = 0; j < answer.length; j++) {
          if (answer[j].properties.name !== '') {
            answers.push({
              id: answer[j].properties.countId,
              name: answer[j].properties.name,
            });
          }
        }
      }

      return answers;
    },
    getAllAnswerMentionFormula: function(list, questionCount, answerCount) {
      var answers = [];

      if (list.length <= 0) {
        return answers;
      }

      for (var i = 0; i < list.length; i++) {
        if (list[i].properties.answers.length <= 0) {
          continue;
        }

        var answer = list[i].properties.answers;
        for (var j = 0; j < answer.length; j++) {
          if (!(list[i].getCountId() === questionCount && answer[j].getCountId() === answerCount) &&
            answer[j].properties.name !== '') {
            answers.push({
              id: answer[j].properties.countId,
              name: answer[j].properties.name,
            });
          }
        }
      }

      return answers;
    },
    availableAnswers: function(list, count) {
      if (list.length <= 0) {
        return;
      }

      for (var i = 0; i < list.length; i++) {
        if (list[i].getCountId() === count) {
          return list[i].properties.answers;
        }
      }

      return;
    },
    getAnswerObject: function(list, questionCount, answerCount) {
      if (list.length <= 0) {
        return;
      }

      for (var i = 0; i < list.length; i++) {
        if (list[i].getCountId() === questionCount) {
          for (var j = 0; j < list[i].properties.answers.length; j++) {
            if (list[i].properties.answers[j].properties.countId === answerCount) {
              return list[i].properties.answers[j];
            }
          }
        }
      }

      return;
    },
    getAnswerType: function(list, questionCount, answerCount) {
      var obj = QuestionReference.getAnswerObject(list, questionCount, answerCount);

      if (obj) {
        return obj.getType();
      }

      return;
    },
    getAnswerValues: function(list, questionCount, answerCount) {
      var options = [{id: 0, name: 'Null'}];
      var obj = QuestionReference.getAnswerObject(list, questionCount, answerCount);

      if (!obj) {
        return;
      }

      var type = obj.getType();

      if (!obj.hasDependency() && (type === '2' || type === '3' || type === '6')) {
        var item = obj.properties.option.properties;
        if (item.data.length > 0) {
          var nameIndex = item.defaultLanguage.iso6391;
          for (var i = 0; i < item.data.length; i++) {
            var optionData = {
              id: item.data[i].id,
              name: item.data[i][nameIndex]
            }
            options.push(optionData);
          }
        }
      } else if (obj.hasDependency() && (type === '2' || type === '3' || type === '6')) {
        var list = obj.properties.dependency.list;

        for (var i = 0; i < list.length; i++) {
          var items = angular.isDefined(list[i].option) ? list[i].option.properties : null;

          if (items == null) {
            continue;
          }

          var nameIndex = angular.isDefined(items.defaultLanguage) ? items.defaultLanguage.iso6391 : 'en';

          for (var j = 0; j < items.data.length; j++) {
            var optionData = {
              id: items.data[j].id,
              name: items.data[j][nameIndex]
            }
            options.push(optionData);
          }
        }
      }

      return options;
    }
  }

  return QuestionReference;
}]);
