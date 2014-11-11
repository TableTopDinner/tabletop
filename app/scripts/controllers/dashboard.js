'use strict';

/**
 * @ngdoc function
 * @name tabletopApp.controller:DashboardCtrl
 * @description
 * # DashboardCtrl
 * Controller of the tabletopApp
 */
angular.module('tabletopApp')
  .controller('DashboardCtrl', function ($scope) {
    
    $scope.alert = function(){
		alert("HEELO");
		};

		//PRODUCTION DB:'https://tabletopdinner.firebaseio.com/' ||| STAGING DB:'https://tabletopstaging.firebaseio.com/'
    var tableRef = new Firebase('https:////tabletopstaging.firebaseio.com/'); 
    
    //Instantiating a new array for restaurants 
    $scope.restaurants = []; 

		//Taking a `snapshot` or `image` of our database stored in the `tableRef` reference to use in our app
    var j = 0;
    tableRef.once('value', function(allSnapshot) {
    		//For each restaurant in our DB
        allSnapshot.forEach(function(restaurantSnapshot) {
            var i = restaurantSnapshot.child('id').val();
            if( i !== null ){
              $scope.restaurants[j] = campaignSnapshot.val();
            }

            $scope.$apply($scope.restaurants);
            console.log("Restaurant: " + j);    
       });
        console.log("Restaurants All Loaded");   
    });

    //Created selectedRestaurant with data retrieved from database to be used within the views.
    $scope.selectRestaurant = function(object) {
        $scope.selectedRestaurant = object;
    };

}); //This is the end of the page dum dum dum (angular.module) ("'\(>.<)/") <-- zach 




//************************************** ANALYSIS **************************************??