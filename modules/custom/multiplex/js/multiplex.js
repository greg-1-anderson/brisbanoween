(function ($, Drupal) {
  Drupal.behaviors.myModuleBehavior = {
    attach: function (context, settings) {
      if (context == document && settings.multiplex.inventory.enabled !== false) {
      	console.log(settings);
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
				// Create the map
				let myMap = new SpookyMap({
					width: "100vw",							// The width of the map + control bar (100vw = full width)
					height: "100vh",						// The height of the map + control bar (100vh = full height)
					allowMapTypeToggle: settings.multiplex.map.config.allowChangeMapType,				// Can the user switch between street view and satellite view
					defaultStreetMapType: settings.multiplex.map.config.useRoadmap, 					// Is street view the default view (true), or satellite (false)
					allowStreetView: settings.multiplex.map.config.allowStreetView,						// Whether to allow the user to zoom all the way into street view
					mapOpacity: settings.multiplex.map.config.mapOpacity,							// How opaque the map is.  The lower this value, the more the backgorund
																	// image will bleed through (0 = hidden, 1 = fully opaque, 0.5 = half visible, etc...)
					backgroundImage: settings.multiplex.map.config.backgroundImage,		// A repeating background image, or one that is the exact size of the map
					visitedIconImage: settings.multiplex.map.config.visitedIcon,			// The URL of the icon to use for locations that have codes
					unvisitedIconImage: settings.multiplex.map.config.unvisitedIcon,		// The URL of the icon to use for locations without codes
					linkBaseURL: settings.multiplex.map.config.linkPrefix,				// The URL to prefix codes with when creating links

					visitedName: settings.multiplex.map.config.visitedName,						// What to call unvisited locations in the legend
					unvisitedName: settings.multiplex.map.config.unvisitedName,					// What to call visited locations in the legend

					centerMapPosition: settings.multiplex.map.config.centerPosition,		// Where to center the map
					defaultZoomLevel: settings.multiplex.map.config.zoomLevel,					// How close to zoom in, the higher the closer to street level it will zoom

					openLinksInNewWindow: settings.multiplex.map.config.openLinksInNewWindow,				// Whether to open links in a new window (true) or redirect the current window (false)
				});

				// Google callback to initialize map contents (in our case, this is when we attach our map)
				window.initMap = () => {
					myMap.attach(document.body);

					// Make an API call to get the location data (must be on the same domain, or have special headers to allow cross-domain requests)
					function updateMap() {
						fetch(settings.multiplex.map.config.apiEndpoint).then(response => response.text()).then(data => {
							myMap.setLegend(JSON.parse(data).legend);
							myMap.setLocations(JSON.parse(data).locations);
						});
					};
					updateMap();
					if (settings.multiplex.map.config.updateFrequency > 0) {
						setInterval(() => {
							updateMap();
						}, settings.multiplex.map.config.updateFrequency * 1000);
					}
				}

				if (google != null && google.maps) {
					initMap();
				}

			}
    }
  };
})(jQuery, Drupal);
