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

  .controller('DashboardCtrl', function ($scope, user, fbutil, $timeout, $firebase, $http) {
    // Changes the layout in dashboard when side-bar navigations are clicked
    $scope.mainShow = true;
    $scope.restaurantsShow = false;
    $scope.eventsShow = false;
    $scope.analysisShow = false;
    $scope.paymentsShow = false;
    $scope.locationsShow = false;
    $scope.dashboardTemplateLoader = function(template) {
         if(template == 'main') {
             $scope.mainShow = true; $scope.restaurantsShow = false; $scope.eventsShow = false; $scope.analysisShow = false; $scope.paymentsShow = false; $scope.locationsShow = false;
         }
         else if(template == 'restaurants') {
             $scope.mainShow = false; $scope.restaurantsShow = true; $scope.eventsShow = false; $scope.analysisShow = false; $scope.paymentsShow = false; $scope.locationsShow = false;
         }
         else if(template == 'events') {
             $scope.mainShow = false; $scope.restaurantsShow = false; $scope.eventsShow = true; $scope.analysisShow = false; $scope.paymentsShow = false; $scope.locationsShow = false;
         }
         else if(template == 'analysis') {
             $scope.mainShow = false; $scope.restaurantsShow = false; $scope.eventsShow = false; $scope.analysisShow = true; $scope.paymentsShow = false; $scope.locationsShow = false;
         }
         else if(template == 'payments') {
             $scope.mainShow = false; $scope.restaurantsShow = false; $scope.eventsShow = false; $scope.analysisShow = false; $scope.paymentsShow = true; $scope.locationsShow = false;
         }
         else if(template == 'locations') {
             $scope.mainShow = false; $scope.restaurantsShow = false; $scope.eventsShow = false; $scope.analysisShow = false; $scope.paymentsShow = false; $scope.locationsShow = true;
         }
    }

    $scope.map = { center: {latitude: 33.636453, longitude: -112.410736}, zoom: 10 };

    //Load our current user, in order to create restaurants for this user
    $scope.user = user;
    loadProfile(user);

    function loadProfile(user) {
      if( $scope.profile ) {
        $scope.profile.$destroy();
      }
      fbutil.syncObject('users/'+user.uid).$bindTo($scope, 'profile');
    }

    //PRODUCTION DB:'https://tabletopdinner.firebaseio.com/' ||| STAGING DB:'https://tabletopstaging.firebaseio.com/'
    // var tableRef = new Firebase('https://tabletopstaging.firebaseio.com/');
    var restaurantsRef = new Firebase('https://tabletopstaging.firebaseio.com/restaurants/');
    var callRestaurantRef = $firebase(restaurantsRef); //<<< Constructor for firebase

    var eventsRef = new Firebase('https://tabletopstaging.firebaseio.com/events/');
    var callEventRef = $firebase(eventsRef); //<<< Constructor for firebase

    // console.log(callRestaurantRef);

    //Instantiating a new array for restaurants
    $scope.restaurants = [];
    //Instantiating a new array for restaurants
    $scope.events = [];

    //To identify when all restaurants are loaded to show them all in sync
    $scope.restaurantsLoaded = false;
    $scope.eventsLoaded = false;

    //Taking a `snapshot` or `image` of our database stored in the `tableRef` reference to use in our app
    restaurantsRef.once('value', function(allSnapshot) {
        //For each restaurant in our DB
        allSnapshot.forEach(function(restaurantSnapshot) {
            var i = restaurantSnapshot.child('id').val();
            var u = restaurantSnapshot.child('userID').val(); //var to create array of only the values with correct current user's id

            if( i !== null && u === user.id){
              // set database content into our restaurants array
              $scope.restaurants[i] = restaurantSnapshot.val();
            }

            $scope.$apply($scope.restaurants);
            console.log("Restaurant: " + i);
       });
      console.log("All restaurants loaded after this call");
      $scope.restaurantsLoaded = true;
      console.log('Array', $scope.restaurants);
    });


    eventsRef.once('value', function(allSnapshot) {
        //For each restaurant in our DB
        allSnapshot.forEach(function(eventSnapshot) {
            var i = eventSnapshot.child('id').val();
            // var r = eventSnapshot.child('rID').val();

            if( i !== null ){
              // set database content into our events array
              $scope.events[i] = eventSnapshot.val();
            }

            $scope.$apply($scope.events);
            console.log("Event: " + i);
       });
      console.log("All events loaded after this call");
      $scope.eventsLoaded = true;
      console.log('Array', $scope.events);
    });


    //Refreshes the add restaurant modal everytime it is selected (clicked on).
    $scope.instantiateRestaurant = function() {
      $scope.selectedRestaurant = {};
      // $scope.selectedRestaurant.name = ""; Name can not be empty
      $scope.selectedRestaurant.description = "";
      $scope.selectedRestaurant.type = ""; //Types: BBQ, Steakhouse, Bar and Grill, Italian, Wine Bar, Mexican
      $scope.selectedRestaurant.website = "";
      $scope.selectedRestaurant.address1 = "";
      $scope.selectedRestaurant.address2 = "";
      $scope.selectedRestaurant.city = "";
      $scope.selectedRestaurant.state = "";
      $scope.selectedRestaurant.zipcode = "";
      $scope.selectedRestaurant.email = "";
      $scope.selectedRestaurant.phoneNum = "";
      $scope.selectedRestaurant.userID = user.id;

      //Removes alerts if there previously
      document.getElementById("missingFieldError").innerHTML = "";
    }

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
          // if(result === true) {
          dataObject = {
              "id": creatingID, "name" : $scope.selectedRestaurant.name, "description": $scope.selectedRestaurant.description, "type": $scope.selectedRestaurant.type, "website": $scope.selectedRestaurant.website, "address1": $scope.selectedRestaurant.address1, "address2": $scope.selectedRestaurant.address2, "city": $scope.selectedRestaurant.city, "state": $scope.selectedRestaurant.state, "zipcode": $scope.selectedRestaurant.zipcode, "email": $scope.selectedRestaurant.email, "phoneNum": $scope.selectedRestaurant.phoneNum, "userID": $scope.selectedRestaurant.userID
          };
          // } else {
          //     dataObject = {
          //         "name" : $scope.selectedRestaurant.name, "description": $scope.selectedRestaurant.description, "address": $scope.selectedRestaurant.address, "email": $scope.selectedRestaurant.email, "phoneNum": $scope.selectedRestaurant.phoneNum, "license": $scope.selectedRestaurant.license
          //     };
          // }

          // Uploading the Data here so AJAX for Restaurant Goes here

          var restAjaxData = {
            "postId": 2000,
            "editLock": null,
            "editLast": null,
            "contactName": "Cory Is Cool",
            "contactTitle": "Cory Sorry",
            "contactStreet": null,
            "contactState": null,
            "contactPostalCode": null,
            "contactCountry": null,
            "contactPhone": null,
            "website": null,
            "facebook": null,
            "twitter": null,
            "wpAttachedFile": null,
            "wpAttachmentMetaData": null
          };

          $.ajax({
              type: "POST",
              dataType: "json",
              crossDomain: true,
              contentType: "application/x-www-form-urlencoded",
              url: "http://www.tabletopdine.com/insertRestaurant.php",
              data: restAjaxData
            }).done(function (response) {
                if (response.success) {
                    alert('Saved!');
                } else {
                    alert('Some error occurred.');
                }
               });

          // $http.post( "http://postbin.hackyon.com/6573FC1D11", restAjaxData );

          //$http.jsonp( "http://www.tabletopdine.com/insertRestaurant.php?callback=JSON_CALLBACK&data=" + restAjaxData);

          restaurantsRef.child(creatingID).set(dataObject);
          location.reload();
          //wait 3000 mili secs as default to give time to load image.
          // (Need It For Image Upload Ignore For Now) $timeout( function() {spinner.stop(); $scope.restuarant.push(dataObject); document.getElementById("missingFieldError").innerHTML = "<div class='alert alert-success'> <strong>Success!</strong>";}, 3000, true);
      }
      //If there are missing field errors alert that silly reatuarnt owner
      catch(err) {
          $('#missingFieldError').show();
          alert(err);
          if ( err.message === "Cannot read property 'name' of undefined") {
              document.getElementById("missingFieldError").innerHTML = "<div class='alert alert-danger'> <strong>Hey you mighty reatuarnt owner!</strong> Please give your restaurant a name.";
          }
          if ( err.message === "Firebase.set failed: First argument contains undefined in property 'name'") {
              document.getElementById("missingFieldError").innerHTML = "<div class='alert alert-danger'> <strong>Hey you mighty reatuarnt owner!</strong> Please give your res a name.";
          }
      }
    }



    $scope.selectEvent = function(object) {
       $scope.selectedEvent = object;
    };

    //Refreshes the add restaurant modal everytime it is selected (clicked on).
    $scope.instantiateEvent = function() {
      $scope.selectedEvent = {};
      // $scope.selectedRestaurant.name = ""; Name can not be empty
      $scope.selectedEvent.description = "";
      $scope.selectedEvent.restaurant = "";
      $scope.selectedEvent.endDate = "";
      $scope.selectedEvent.price = "0";
      $scope.selectedEvent.image = "https://media.licdn.com/media/p/5/000/283/112/1959ca5.png";
      // $scope.selectedEvent.rID = ;

      //Removes alerts if there previously
      document.getElementById("missingFieldError").innerHTML = "";
    }

    $scope.createEvent = function() {

      //The new restaurant about to be created will always be added to the end of the array
      var creatingID = $scope.events.length;

      //If no missing field errors continue
      try {
          // (Need It For Image Upload Ignore For Now) document.getElementById("missingFieldError").innerHTML = "<div class='alert alert-info'> <strong>Loading Restuarant...</strong>";


//enddate, amountSaved,

          var dataObject;
          // if(result === true) {
          dataObject = {
              "id": creatingID, "name" : $scope.selectedEvent.name, "description": $scope.selectedEvent.description, "restaurant": $scope.selectedEvent.restaurant, "price": $scope.selectedEvent.price, "image": $scope.selectedEvent.image, "endDate": $scope.selectedEvent.endDate
          };
          // } else {
          //     dataObject = {
          //         "name" : $scope.selectedRestaurant.name, "description": $scope.selectedRestaurant.description, "address": $scope.selectedRestaurant.address, "email": $scope.selectedRestaurant.email, "phoneNum": $scope.selectedRestaurant.phoneNum, "license": $scope.selectedRestaurant.license
          //     };
          // }

          // Uploading the Data here so AJAX for Events Goes here
          var eventAjaxData = {
            "postId": 2000,
            "basePrice": $scope.selectedEvent.price,
            "amountSaved": $scope.selectedEvent.price,
            "highlights": $scope.selectedEvent.description
          };
          $.ajax({
             type: "POST",
             dataType: "json",
             url: "www.tabletopdine.com/insertEvent.php", //Relative or absolute path to response.php file
             data: eventAjaxData,
             success: function(data) {
                     alert("Form submitted successfully.\nReturned json: " + data["json"]);
             },
             error: function(XMLHttpRequest, textStatus, errorThrown) {
                   alert("Status: " + textStatus); alert("Error: " + errorThrown);
             }
           });

          eventsRef.child(creatingID).set(dataObject);
          console.log("Success?");
          location.reload();
          //wait 3000 mili secs as default to give time to load image.
          // (Need It For Image Upload Ignore For Now) $timeout( function() {spinner.stop(); $scope.restuarant.push(dataObject); document.getElementById("missingFieldError").innerHTML = "<div class='alert alert-success'> <strong>Success!</strong>";}, 3000, true);
      }
      //If there are missing field errors alert that silly reatuarnt owner
      catch(err) {
          $('#missingFieldError').show();
          alert(err);
          if ( err.message === "Cannot read property 'name' of undefined") {
              document.getElementById("missingFieldError").innerHTML = "<div class='alert alert-danger'> <strong>Hey you mighty reatuarnt owner!</strong> Please give your event a name.";
          }
          if ( err.message === "Firebase.set failed: First argument contains undefined in property 'name'") {
              document.getElementById("missingFieldError").innerHTML = "<div class='alert alert-danger'> <strong>Hey you mighty reatuarnt owner!</strong> Please give your event a name.";
          }
      }
    }

    $scope.selectRestaurantAdd = function(restaurant) {
      alert(restaurant.name);
        document.getElementById("restaurantAddInput").value = restaurant.name;
        $scope.selectedEvent.restaurant = restaurant.id; //can do restaurant.id
    };


}); //This is the end of the page dum dum dum (angular.module) ("'\(>.<)/") <-- "that is zach"
