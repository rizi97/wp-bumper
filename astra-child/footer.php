<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Astra
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

?>
<?php astra_content_bottom(); ?>
</div> <!-- ast-container -->
</div><!-- #content -->
<?php
astra_content_after();

astra_footer_before();

astra_footer();

astra_footer_after();
?>
</div><!-- #page -->
<?php
astra_body_bottom();
wp_footer();
?>

<script id="myScript" async defer></script>

<script>
	let apiKey = '<?php echo google_map_key; ?>';
	let _src = "https://maps.googleapis.com/maps/api/js?key=" + apiKey + "&libraries=places&callback=initMap";

	document.getElementById('myScript').src = _src;



	function initMap() {
		var myLatLng = { lat: 31.5165, lng: 74.3499 };

		jQuery.get({
			url: `https://maps.googleapis.com/maps/api/geocode/json?latlng=${myLatLng.lat},${myLatLng.lng}&sensor=false&key=${apiKey}`, success(data) {
				let _data = data.results[0].formatted_address;

				jQuery('#billing_address_1').val(_data);

				jQuery('#user_location_lat').val(myLatLng.lat);
				jQuery('#user_location_long').val(myLatLng.lng);
			}
		});



		var map = new google.maps.Map(document.getElementById('map'), {
			center: myLatLng, // Set your desired map center
			zoom: 10 // Set your desired zoom level
		});


		// const cityCircle = new google.maps.Circle({
		// 	strokeColor: "#FF0000",
		// 	strokeOpacity: 0.8,
		// 	strokeWeight: 2,
		// 	fillColor: "#FF0000",
		// 	fillOpacity: 0.35,
		// 	map,
		// 	center: myLatLng,
		// 	radius: Math.sqrt(63502) * 100,
		// });

		// Define the boundaries of your map
		var mapBounds = new google.maps.LatLngBounds(
			new google.maps.LatLng(31.3747, 74.1343),  // Southwest corner of Lahore
			new google.maps.LatLng(31.6648, 74.4445) // Northeast corner
		);

		// Create a new marker
		var marker = new google.maps.Marker({
			position: myLatLng,
			map: map,
			title: 'Marker',
			draggable: true
		});

		var originalPosition;

		// Add an event listener for dragstart
		google.maps.event.addListener(marker, 'dragstart', function () {
			originalPosition = marker.getPosition();
		});

		// Add an event listener for dragend
		google.maps.event.addListener(marker, 'dragend', function (m) {
			if (!mapBounds.contains(marker.getPosition())) {
				marker.setPosition(originalPosition);
			}
			else {
				// console.log(m.latLng.lat())
				let lat = m.latLng.lat();
				let longi = m.latLng.lng();

				jQuery.get({
					url: `https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${longi}&sensor=false&key=${apiKey}`, success(data) {
						let _data = data.results[0].formatted_address;

						jQuery('#billing_address_1').val(_data);

						jQuery('#user_location_lat').val(lat);
						jQuery('#user_location_long').val(longi);
					}
				});
			}
		});

		// Add a click event listener to the map
		google.maps.event.addListener(map, 'click', function (event) {
			if (mapBounds.contains(event.latLng)) {
				// Remove the previous marker (if exists)
				if (marker) {
					marker.setMap(null);
				}

				// Create a new marker at the clicked position
				marker = new google.maps.Marker({
					position: event.latLng,
					map: map,
					draggable: true
				});

				// Center the map on the clicked position
				map.setCenter(event.latLng);

				let lat = event.latLng.lat();
				let longi = event.latLng.lng();

				jQuery.get({
					url: `https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${longi}&sensor=false&key=${apiKey}`, success(data) {
						let _data = data.results[0].formatted_address;

						jQuery('#billing_address_1').val(_data);

						jQuery('#user_location_lat').val(lat);
						jQuery('#user_location_long').val(longi);
					}
				});


				google.maps.event.addListener(marker, 'dragend', function (m) {
					if (!mapBounds.contains(marker.getPosition())) {
						marker.setPosition(originalPosition);
					}
					else {
						// console.log(m.latLng.lat())
						let lat = m.latLng.lat();
						let longi = m.latLng.lng();

						jQuery.get({
							url: `https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${longi}&sensor=false&key=${apiKey}`, success(data) {
								let _data = data.results[0].formatted_address;

								jQuery('#billing_address_1').val(_data);

								jQuery('#user_location_lat').val(lat);
								jQuery('#user_location_long').val(longi);
							}
						});
					}
				});
			}
		});


	}



</script>

</body>

</html>