'use strict';

angular.module('formBuilder')
.factory('OptionsItem', [
  'Options',
  function(Options) {
  return function(id) {
    var properties = {
      id: id,
      name: '',
      languages: [],
      defaultLanguage: {},
      data: [],
      enabled: true
    };

    var optionsItem = {
      properties: properties,
      addData: function() {
        properties.data.push({});
      },
      getName: function() {
        return properties.name;
      },
      getLanguages: function() {
        return properties.languages;
      },
      getData: function() {
        return properties.data;
      },
      getDefaultLanguage: function() {
        return properties.defaultLanguage;
      },
      fetch: function() {
        if (properties.id) {
          Options.get(properties.id).then(function(response) {
            if (response !== null) {
              optionsItem.setFromObject(response);
            }
          });
        }
      },
      setFromObject: function(obj) {
        if (obj) {
          optionsItem.setData(obj.data);
          optionsItem.setId(obj.id);
          optionsItem.setName(obj.name);
          optionsItem.setDefaultLanguage(obj.defaultLanguage);
          optionsItem.setLanguages(obj.languages);
          optionsItem.setEnabled(obj.enabled);
        }
      },
      setData: function(obj) {
        properties.data.splice(0, properties.data.length);
        if (angular.isObject(obj)) {
          for (var i = 0; i < obj.length; i++) {
            properties.data.push(obj[i]);
          }
        }
      },
      setEnabled: function(enabled) {
        properties.enabled = enabled;
      },
      setLanguages: function(languages) {
        properties.languages = languages;
      },
      setId: function(id) {
        properties.id = id;
      },
      setName: function(name) {
        properties.name = name;
      },
      setDefaultLanguage: function(defaultLanguage) {
        properties.defaultLanguage = defaultLanguage;
      }
    };

    return optionsItem;
  };
}]);
