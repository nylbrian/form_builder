'use strict';

angular.module('formBuilder')
.factory('QuestionsList', [
  'QuestionsItem',
  function(QuestionsItem) {
  return function(data) {
    var list = [];
    var selected = {};
    var counter = 0;

    var QuestionList = {
      list: list,
      addItem: function(node, id) {
        if (!angular.isDefined(node)) {
          node = list;
        }

        var newNode = new QuestionsItem(id);
        newNode.addRoute();
        newNode.addAnswer();
        newNode.setCountId(counter);
        node.push(newNode);
        selected = node[node.length - 1];
        counter++;
      },
      copyItem: function(source, destination) {
        source.setCountId(counter);
        destination.push(angular.copy(source));
        selected = destination[destination.length - 1];
      },
      getSelected: function() {
        return selected;
      },
      isSelectedItem: function(obj) {
        if (selected === null) {
          return false;
        }

        if (Object.keys(selected).length  == null) {
          return false;
        }
        return selected.$$hashKey === obj.$$hashKey;
      },
      setSelected: function(obj) {
        selected = obj;
      },
      removeItem: function() {
        selected = {};
      },
      setList: function(questionList) {
        list.splice(0, list.length);

        angular.forEach(questionList, function(value, key) {
          var node = new QuestionsItem();
          node.setProperties(value.properties);
          list.push(node);
          counter++;
          selected = list[0];
        });
      }
    };

    return QuestionList;
  };
}]);
