/**
 * Created by csiebler on 2/19/15.
 */
/**
 * @ngdoc function
 * @name tabletopApp.directive:googleMap
 * @description
 * # googleMapDirective
 * Displays a Map using the Google Map API
 */
angular.module('tabletopApp')
  .directive('googleMap', function () {
    return {
      restrict: 'E',
      replace: true,
      template: '<div></div>',
      link: function($scope, element, attrs) {
        var center = new google.maps.LatLng(33.448377, -112.074037);

        var map_options = {
          zoom: 12,
          center: center,
          mapTypeId: google.maps.MapTypeId.SATELLITE
        };

        // create map
        var map = new google.maps.Map(document.getElementById(attrs.id), map_options);

        // configure marker
        var marker_options = {
          map: map,
          position: center
        };

        // create marker
        var marker = new google.maps.Marker(marker_options);

        $scope.$watch('locationsShow', function () {
          window.setTimeout(function(){
            google.maps.event.trigger(map, 'resize');
          },100);
        });
      }
    }
  });
