(function ($, Drupal) {
  Drupal.behaviors.myModuleBehavior = {
    attach: function (context, settings) {
      if (context == document && settings.multiplex.inventory.enabled !== false) {
				// Create the inventory widget
				let inventoryBox = new InventoryBox(
				  // Configuration
					settings.multiplex.inventory.config,

					// Map of items
					settings.multiplex.inventory.items
				);

				// Attach it to the page (give it a second, so we drop below the privacy policy - this is a hack)
				setTimeout(() => {
					inventoryBox.attach(document.body);
				}, 10);
			}
			if (context == document && settings.multiplex.map.enabled !== false) {
				console.log(settings);
				// Create the map
				let myMap = new SpookyMap({
					width: "100vw",							// The width of the map + control bar (100vw = full width)
					height: "100vh",						// The height of the map + control bar (100vh = full height)
					allowMapTypeToggle: false,				// Can the user switch between street view and satellite view
					defaultStreetMapType: true, 					// Is street view the default view (true), or satellite (false)
					allowStreetView: false,						// Whether to allow the user to zoom all the way into street view
					mapOpacity: 0.8,							// How opaque the map is.  The lower this value, the more the backgorund
																	// image will bleed through (0 = hidden, 1 = fully opaque, 0.5 = half visible, etc...)
					backgroundImage: settings.multiplex.map.backgroundImage,		// A repeating background image, or one that is the exact size of the map
					visitedIconImage: settings.multiplex.map.visitedIcon,			// The URL of the icon to use for locations that have codes
					unvisitedIconImage: settings.multiplex.map.unvisitedIcon,		// The URL of the icon to use for locations without codes
					linkBaseURL: 'https://g1a.io/',				// The URL to prefix codes with when creating links

					visitedName: 'Visited',						// What to call unvisited locations in the legend
					unvisitedName: 'Unvisited',					// What to call visited locations in the legend

					centerMapPosition: { lat: 37.689846, lng: -122.402881 },		// Where to center the map
					defaultZoomLevel: 14,											// How close to zoom in, the higher the closer to street level it will zoom

					openLinksInNewWindow: false,				// Whether to open links in a new window (true) or redirect the current window (false)
				});

				// Google callback to initialize map contents (in our case, this is when we attach our map)
				window.initMap = () => {
					myMap.attach(document.body);

					myMap.setLocations(settings.multiplex.map.locations);
				}

				if (google != null && google.maps) {
					initMap();
				}

			}
    }
  };
})(jQuery, Drupal);
