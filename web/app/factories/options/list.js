'use strict';

angular.module('formBuilder')
.factory('OptionsList', [
  'Options', 'OptionsItem',
  function(Options, OptionsItem) {
  return function(id) {
    var list = [];
    var selected = {};

    var OptionList = {
      selected: selected,
      addItem: function(id) {
        list.push(new OptionsItem(id));
        selected = list[list.length - 1];
      },
      isSelected: function(item) {
        return selected.$$hashKey === item.$$hashKey;
      },
      getList: function() {
        return list;
      },
      getSelected: function() {
        return selected;
      },
      setSelected: function(item) {
        selected = item;
      },
    };

    Options.getList().then(function(response) {
      if (response.length > 0) {
        angular.forEach(response, function(value, key) {
          var optionItem = new OptionsItem(id);
          optionItem.setFromObject(value);
          list.push(optionItem);

          if (id === value.id) {
            selected = list[list.length - 1];
          }
        });
      }
    });

    return OptionList;
  };
}]);
