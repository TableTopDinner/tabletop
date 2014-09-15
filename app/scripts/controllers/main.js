'use strict';

/**
 * @ngdoc function
 * @name tabletopApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the tabletopApp
 */
angular.module('tabletopApp')
  .controller('MainCtrl', function ($scope) {
    $scope.awesomeThings = [
      'HTML5 Boilerplate',
      'AngularJS',
      'Karma'
    ];
  });
