'use strict';
/**
 * @ngdoc function
 * @name tabletopApp.controller:LoginCtrl
 * @description
 * # LoginCtrl
 * Manages authentication to any active providers.
 */
angular.module('tabletopApp')
  .controller('LoginCtrl', function ($scope, simpleLogin, $location) {
    $scope.oauthlogin = function(provider) {
      login(provider, {
        rememberMe: true
      });
    };

    $scope.passwordLogin = function(email, pass) {
      login('password', {
        email: email,
        password: pass,
        rememberMe: true
      });
    };

    $scope.createAccount = function(email, pass, confirm) {
      $scope.err = null;
      if(!pass) {
        $scope.err = 'Please enter a password';
      } else if(pass !== confirm) {
        $scope.err = 'Passwords do not match';
      } else {
        simpleLogin.createAccount(email, pass/*, name*/)
          .then(function() {
            $location.path('/account');
          }, function(err) {
            $scope.err = err;
          });
      }
    };

    function login(provider, opts) {
      $scope.err = null;
      simpleLogin.login(provider, opts).then(
        function() {
          $location.path('/dashboard');
        },
        function(err) {
          $scope.err = err;
        }
      );
    }

  });
