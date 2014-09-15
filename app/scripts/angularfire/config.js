angular.module('firebase.config', [])
  .constant('FBURL', 'https://tabletopstaging.firebaseio.com')
  .constant('SIMPLE_LOGIN_PROVIDERS', ['password','facebook'])

  .constant('loginRedirectPath', '/login');