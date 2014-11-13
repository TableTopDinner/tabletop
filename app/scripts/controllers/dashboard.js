'use strict';

/**
 * @ngdoc function
 * @name tabletopApp.controller:DashboardCtrl
 * @description
 * # DashboardCtrl
 * Controller of the tabletopApp
 */
angular.module('tabletopApp')
  //Filter to Limit amount of text displayed, used in card views
  .filter('cut', function () {
      return function (value, wordwise, max, tail) {
          if (!value) return '';

          max = parseInt(max, 10);
          if (!max) return value;
          if (value.length <= max) return value;

          value = value.substr(0, max);
          if (wordwise) {
              var lastspace = value.lastIndexOf(' ');
              if (lastspace != -1) {
                  value = value.substr(0, lastspace);
              }
          }
          return value + (tail || ' …');
      };
  })

  .controller('DashboardCtrl', function ($scope, user, fbutil, $timeout) {

    //Load our user, in order to create restaurants for this user
    $scope.user = user;
    loadProfile(user);

    function loadProfile(user) {
      if( $scope.profile ) {
        $scope.profile.$destroy();
      }
      fbutil.syncObject('users/'+user.uid).$bindTo($scope, 'profile');
    }

		//PRODUCTION DB:'https://tabletopdinner.firebaseio.com/' ||| STAGING DB:'https://tabletopstaging.firebaseio.com/'
    var tableRef = new Firebase('https://tabletopstaging.firebaseio.com/');
    // var callTableRef = $firebase(tableRef);

    //Instantiating a new array for restaurants
    $scope.restaurants = [];
    //To identify when all restaurants are loaded to show them all in sync
    $scope.restaurantsLoaded = false;

		//Taking a `snapshot` or `image` of our database stored in the `tableRef` reference to use in our app
    var j = 0;
    tableRef.once('value', function(allSnapshot) {
    		//For each restaurant in our DB
        allSnapshot.forEach(function(restaurantSnapshot) {
            var i = restaurantSnapshot.child('id').val();
            if( i !== null ){
            	// set database content into our restaurants array
              $scope.restaurants[j] = restaurantSnapshot.val();
            }

            $scope.$apply($scope.restaurants);
            console.log("Restaurant: " + j);
       });
      console.log("Restaurants All Loaded");
      $scope.restaurantsLoaded = true;
    });


    // $scope.dashboardTemplateLoader = function(template) {
    //   var template_dom_string = "<div ng-include src=\"\'views/dashboard_templates/" + template + ".html\'\"></div>"

    //   console.log(template_dom_string);

    //   document.getElementById("dashboard_template_loaded_here").innerHTML = "<div ng-include src=\"\'views/dashboard_templates/restaurants.html\'\"></div>";
    // }

    $scope.restaurantsShow = true;
    $scope.eventsShow = false;
    $scope.analysisShow = false;
    $scope.paymentShow = false;
    $scope.dashboardTemplateLoader = function(template) {
         if(template == 'restaurants') {
             $scope.restaurantsShow = true; $scope.eventsShow = false; $scope.analysisShow = false; $scope.paymentShow = false;
         }
         if(template == 'events') {
             $scope.restaurantsShow = false; $scope.eventsShow = true; $scope.analysisShow = false; $scope.paymentShow = false;
         }
         if(template == 'analysis') {
             $scope.restaurantsShow = false; $scope.eventsShow = false; $scope.analysisShow = true; $scope.paymentShow = false;
         }
         if(template == 'payment') {
             $scope.restaurantsShow = false; $scope.eventsShow = false; $scope.analysisShow = false; $scope.paymentShow = true;
         }
    };


    //Refreshes the add restaurant modal everytime it is selected (clicked on).
    $scope.instantiateRestaurant = function(object) {
    	$scope.selectedRestaurant = {};
      // $scope.selectedRestaurant.name = ""; Name can not be empty
      $scope.selectedRestaurant.description = "";
      $scope.selectedRestaurant.type = "";

      //Removes alerts if there previously
      document.getElementById("missingFieldError").innerHTML = "";
    };

    //Instantiate `selectedRestaurant` with data of the currently selected (clicked on) restaurant.
    $scope.selectRestaurant = function(object) {
       $scope.selectedRestaurant = object;
    };

    //Called when 'Create' button is pressed within the Create New Restaurant modal
    $scope.createRestaurant = function() {
    	//The new restaurant about to be created will always be added to the end of the array
    	var creatingID = $scope.restaurants.length;

    	//If no missing field errors continue
      try {
          // (Need It For Image Upload Ignore For Now) document.getElementById("missingFieldError").innerHTML = "<div class='alert alert-info'> <strong>Loading Restuarant...</strong>";

          var dataObject;
          if(result === true) {
              dataObject = {
                  "name" : $scope.selectedRestaurant.name, "description": $scope.selectedRestaurant.description, "type": $scope.selectedRestaurant.type, "imgSrc": resizedImgSrcData, "thumbSrc": resizedThumbSrcData, "brand": $scope.selectedRestaurant.brand, "region": $scope.selectedRestaurant.region, "location": $scope.selectedRestaurant.location, "contact": $scope.selectedRestaurant.contact, "url": $scope.selectedRestaurant.url, "status": $scope.selectedRestaurant.status, "startDate": $scope.selectedRestaurant.startDate, "endDate": $scope.selectedRestaurant.endDate, "canvas": $scope.selectedRestaurant.canvas, "hashtags": $scope.selectedRestaurant.hashtags, "publish": true, "id": addID, "brandImgSrc": resizedBrandImgSrcData, "brandThumbSrc": resizedBrandThumbSrcData
              };
          } else {
              dataObject = {
                  "name" : $scope.selectedRestaurant.name, "description": $scope.selectedRestaurant.description, "type": $scope.selectedRestaurant.type, "imgSrc": resizedImgSrcData, "thumbSrc": resizedThumbSrcData, "brand": $scope.selectedRestaurant.brand, "region": $scope.selectedRestaurant.region, "location": $scope.selectedRestaurant.location, "contact": $scope.selectedRestaurant.contact, "url": $scope.selectedRestaurant.url, "status": $scope.selectedRestaurant.status, "startDate": $scope.selectedRestaurant.startDate, "endDate": $scope.selectedRestaurant.endDate, "canvas": $scope.selectedRestaurant.canvas, "hashtags": $scope.selectedRestaurant.hashtags, "publish": false, "id": addID, "brandImgSrc": resizedBrandImgSrcData, "brandThumbSrc": resizedBrandThumbSrcData
              };
          }

          fireRef.child(creatingID).set(dataObject);
          //wait 3000 mili secs as default to give time to load image.
          // (Need It For Image Upload Ignore For Now) $timeout( function() {spinner.stop(); $scope.restuarant.push(dataObject); document.getElementById("missingFieldError").innerHTML = "<div class='alert alert-success'> <strong>Success!</strong>";}, 3000, true);
      }
      //If there are missing field errors alert that silly reatuarnt owner
      catch(err) {
          $('#missingFieldError').show();
          spinner.stop();
          if ( err.message === "Cannot read property 'name' of undefined") {
              document.getElementById("missingFieldError").innerHTML = "<div class='alert alert-danger'> <strong>Hey you mighty reatuarnt owner!</strong> Please give your restaurant a name.";
          }
          if ( err.message === "Firebase.set failed: First argument contains undefined in property 'name'") {
              document.getElementById("missingFieldError").innerHTML = "<div class='alert alert-danger'> <strong>Hey you mighty reatuarnt owner!</strong> Please give your res a name.";
          }
      }
    }

}); //This is the end of the page dum dum dum (angular.module) ("'\(>.<)/") <-- zach




//************************************** ANALYSIS **************************************??
