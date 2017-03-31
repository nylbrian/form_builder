'use strict';

angular.module('formBuilder')
.factory('OptionsGrid', function() {
  return function(id, languages) {
    var options = {
      columns: [],
      data: []
    };

    var optionsGrid = {
      options: options,
      columns: options.columns,
      data: options.data,
      addRow: function(obj) {
        if (angular.isObject(obj)) {
          options.data.push(obj);
        }
      },
      setData: function(obj) {
        options.data.splice(0, options.data.length);
        if (angular.isObject(obj)) {
          for (var i = 0; i < obj.length; i++) {
            options.data.push(obj[i]);
          }
        }
      },
      setLanguages: function(languageObject) {
        languages = languageObject;
        optionsGrid.updateColumns();
      },
      updateColumns: function() {
        options.columns.splice(0, options.columns.length);
        if (angular.isObject(languages)) {
          for (var i = 0; i < languages.length; i++) {
            options.columns.push({name: languages[i].name, field: languages[i].iso6391});
          }
          options.columns.push({name: 'id', field: 'id', visible: false});
        }
      }
    };

    return optionsGrid;
  };
});
