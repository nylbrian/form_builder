'use strict';

angular.module('formBuilder',[
  'ngRoute',
  'ngAnimate',
  'ngResource',
  'ui.bootstrap',
  'ui.tree',
  'ui.select',
  'ui.grid',
  'ui.grid.edit',
  'ui.grid.cellNav',
  'ui.grid.selection',
  'mentio',
])
.constant('TEMPLATE_URL', 'app/views/')
.constant('MODAL_TEMPLATE_URL', 'app/views/modal/')
.constant('API_URL', '../rest/index.php?request=:controller/:action/:id/:paramOne/:paramTwo/:paramThree/:paramFour')
.constant('ADMIN_ROLE', 1)
.constant('USER_ROLE', 2)
.constant('AUTH_EVENTS', {
  loginSuccess: 'loginSuccess',
  loginFailed: 'loginFailed',
  logoutSuccess: 'logoutSuccess',
  notAuthorized: 'notAuthorized',
  notAuthenticated: 'notAuthenticated'
})
.config(['$animateProvider', function($animateProvider) {
  $animateProvider.classNameFilter(/panel-collapse/);
}])
.config(['$locationProvider', function($locationProvider) {
  $locationProvider.html5Mode({
    enabled: false,
    requireBase: false
  });
}])
.config(['uiSelectConfig', function(uiSelectConfig) {
  uiSelectConfig.theme = 'bootstrap';
  uiSelectConfig.resetSearchInput = true;
  uiSelectConfig.appendToBody = true;
}])
.config(['$routeProvider', function($routeProvider, ADMIN_ROLE, USER_ROLE) {
  $routeProvider
  .when('/questionnaire', {
    title: 'View Questionnaires',
    templateUrl: 'app/views/questions/main.html',
    controller: 'QuestionaireController',
    data: {
      requireLogin: true,
      authorizedRoles: [ADMIN_ROLE, USER_ROLE]
    }
  })
  .when('/questionnaire/export', {
    title: 'Export Questionnaire',
    templateUrl: 'app/views/questions/export.html',
    controller: 'ExportQuestionController',
    data: {
      requireLogin: true,
      authorizedRoles: [ADMIN_ROLE]
    }
  })
  .when('/questionnaire/create', {
    title: 'Create Questionnaire',
    templateUrl: 'app/views/questions/create.html',
    controller: 'CreateQuestionController',
    data: {
      requireLogin: true,
      authorizedRoles: [ADMIN_ROLE]
    }
  })
  .when('/questionnaire/edit/:questionnaireId', {
    title: 'Update Questionnaire',
    templateUrl: 'app/views/questions/edit.html',
    controller: 'EditQuestionController',
    data: {
      requireLogin: true,
      authorizedRoles: [ADMIN_ROLE]
    }
  })
  .when('/options', {
    title: 'View Options',
    templateUrl: 'app/views/options/main.html',
    controller: 'OptionsController',
    data: {
      requireLogin: true,
      authorizedRoles: [ADMIN_ROLE, USER_ROLE]
    }
  })
  .when('/options/create', {
    title: 'Create Option',
    templateUrl: 'app/views/options/create.html',
    controller: 'CreateOptionsController',
    data: {
      requireLogin: true,
      authorizedRoles: [ADMIN_ROLE]
    }
  })
  .when('/options/edit/:optionsId', {
    title: 'Update Option',
    templateUrl: 'app/views/options/edit.html',
    controller: 'EditOptionsController',
    data: {
      requireLogin: true,
      authorizedRoles: [ADMIN_ROLE]
    }
  })
  .when('/users', {
    title: 'View Users',
    templateUrl: 'app/views/users/main.html',
    controller: 'UsersController',
    data: {
      requireLogin: true,
      authorizedRoles: [ADMIN_ROLE, USER_ROLE]
    }
  })
  .when('/users/create', {
    title: 'Create User',
    templateUrl: 'app/views/users/create.html',
    controller: 'CreateUsersController',
    data: {
      requireLogin: true,
      authorizedRoles: [ADMIN_ROLE]
    }
  })
  .when('/users/edit/:userId', {
    title: 'Update User',
    templateUrl: 'app/views/users/edit.html',
    controller: 'EditUsersController',
    data: {
      requireLogin: true,
      authorizedRoles: [ADMIN_ROLE]
    }
  })
  .when('/home', {
    title: 'Home',
    templateUrl: 'app/views/main.html',
    controller: 'HomeController',
    data: {
      requireLogin: true,
      authorizedRoles: [ADMIN_ROLE, USER_ROLE]
    }
  })
  .when('/settings', {
    title: 'Settings',
    templateUrl: 'app/views/settings.html',
    controller: 'SettingsController',
    data: {
      requireLogin: true
    }
  })
  .when('/login', {
    title: 'Login',
    templateUrl: 'app/views/login.html',
    controller: 'LoginController',
    data: {
      requireLogin: false
    }
  })
  .when('/logout', {
    title: 'Login',
    templateUrl: 'app/views/logout.html',
    controller: 'LogoutController',
    data: {
      requireLogin: true
    }
  })
  .otherwise({
    title: '',
    redirectTo: '/home'
  });
}])
.run(['$rootScope', 'Session', 'Users', 'AUTH_EVENTS',
  function($rootScope, Session, Users, AUTH_EVENTS) {

  $rootScope.$on('$routeChangeSuccess', function (event, current, previous) {
    if (current.$$route) {
      if (current.$$route.data.requireLogin === true && !Session.isLoggedIn()) {
        if (Session.getValidated() === false) {
          Users.getLoggedUser().then(function(response) {
            if (response.status === 'success') {
              Session.create(
                response.data.userId,
                response.data.userName,
                response.data.userRole,
                response.data.realName
              );
              $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
            } else {
              $rootScope.$broadcast(AUTH_EVENTS.notAuthenticated);
            }
          }, function() {
            $rootScope.$broadcast(AUTH_EVENTS.notAuthenticated);
          }).finally(function() {
            Session.setValidated(true);
          });
        } else {
          $rootScope.$broadcast(AUTH_EVENTS.notAuthenticated);
        }
      }

      $rootScope.title = current.$$route.title;
      $rootScope.loaded = true;
    }
  });
}]);
