var ContactUs = function () {

    return {
        //main function to initiate the module
        init: function (lat, lng, address, title) {
			var map;
			$(document).ready(function(){
			  map = new GMaps({
				div: '#map',
				lat: lat,
				lng: lng
			  });
			   var marker = map.addMarker({
		            lat: lat,
					lng: lng,
		            title: title,
		            infoWindow: {
		                content: address
		            }
		        });

			   marker.infoWindow.open(map, marker);
			});
        }
    };

}();