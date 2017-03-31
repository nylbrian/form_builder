'use strict';

angular.module('formBuilder')
.factory('OptionsFactory', function() {
  return function(id, languages, columnSettings) {
    var optionId = id;
    var optionLanguages = languages;
    var settings = {};
    var options = {
      columns: [{
        field: 'optionId',
        visible: false
      }],
      data: []
    };

    if (angular.isObject(columnSettings)) {
      settings = columnSettings;
      var defaultSettings = {
        enableHiding: false
      };
    }

    var objectMethods = {
      addRow: function() {
        options.data.push({optionId: ''});
      },
      getOptions: function() {
        return options;
      },
      getColumns: function() {
        return options.columns;
      },
      getData: function() {
        return options.data;
      },
      setData: function(data) {
        options.data.splice(0, options.data.length);
        options.data.extend(data);
      },
      getId: function() {
        return optionId;
      },
      setLanguages: function(languageObject) {
        optionLanguages = languageObject;
        objectMethods.updateColumns();
      },
      updateColumns: function() {
        options.columns.splice(1, options.columns.length);
        for (var i = 0; i < optionLanguages.length; i++) {
          options.columns.push({field: optionLanguages[i].name});
        }
      }
    };

    return objectMethods;
  };
});
