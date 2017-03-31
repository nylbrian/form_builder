'use strict';

angular.module('formBuilder')
.service('Session', function() {
  var userSession = {
    userId: null,
    username: null,
    realname: null,
    userRole: null,
    validated: false
  };

  return {
    create: function(userId, username, userRole, realname) {
      userSession.userId = userId;
      userSession.username = username;
      userSession.userRole = userRole;
      userSession.realname = realname;
      userSession.validated = true;
    },
    destroy: function() {
      userSession.userId = null;
      userSession.username = null;
      userSession.realname = null;
      userSession.userRole = null;
    },
    isLoggedIn: function() {
      return userSession.userId > 0;
    },
    isAuthorized: function(role) {

    },
    setValidated: function(validated) {
      userSession.validated = validated;
    },
    getValidated: function() {
      return userSession.validated;
    },
    getUsername: function() {
      return userSession.username;
    },
    getUser: function() {
      return userSession;
    },
    setRealName: function(name) {
      userSession.realname = name;
    }
  }
});
