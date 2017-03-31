'use strict';

angular.module('formBuilder')
.factory('UsersItem', [
  function() {
  return function() {
    var properties = {
      id: null,
      username: '',
      password: '',
      name: '',
      role: '',
      enabled: true,
      mode: 'add',
      displayPasswords: true,
    };

    var usersItem = {
      properties: properties,
      setUsername: function(username) {
        properties.username = username;
      },
      setName: function(name) {
        properties.name = name;
      },
      setRole: function(role) {
        properties.role = role;
      },
      setEnabled: function(enabled) {
        properties.enabled = enabled;
      },
      setId: function(id) {
        properties.id = id;
      },
      setMode: function(mode) {
        properties.mode = mode;

        if (mode === 'edit') {
          properties.displayPasswords = false;
        }
      },
      setFromObject: function(obj) {
        usersItem.setEnabled(obj.enabled);
        usersItem.setUsername(obj.username);
        usersItem.setName(obj.name);
        usersItem.setId(obj.id);
        usersItem.setRole(obj.role);
      }
    };

    return usersItem;
  };
}]);
