(function ($, Drupal) {
	let orig = Drupal.behaviors.myModuleBehavior ? Drupal.behaviors.myModuleBehavior.attach : null;
  Drupal.behaviors.myModuleBehavior = {
    attach: function (context, settings) {
    	if (orig) {
    		orig(context, settings);
    	}

    	if (context == document) {
    		let docHeader = document.getElementById("header");
    		if (docHeader) {
    			docHeader.style.cursor = "pointer";
    			docHeader.addEventListener("click", () => {
    				let sessionCookie = SpookyMap.getCookies()[settings.multiplex.map.config.sessionCookieName];
    				if (sessionCookie) {
    					document.location = "/landing";
    				}
    				else {
    					document.location = "/";
    				}
    			});
    		}
    	}
      if (context == document && settings.multiplex.inventory.enabled !== false) {
				// Create the inventory widget
				let inventoryBox = new InventoryBox(
				  // Configuration
					settings.multiplex.inventory.config,

					// Map of items
					settings.multiplex.inventory.items
				);

				// Attach it to the page (give it a second, so we drop below the privacy policy - this is a hack)
				if (settings.multiplex.inventory.config.enabled !== false) {
					setTimeout(() => {
						inventoryBox.attach(document.body);

						// Add any links assigned server-side (this can be done client-side too)
						if (settings.multiplex.inventory.config.links) {
							for (id in settings.multiplex.inventory.config.links) {
								InventoryBox.setItemLink(id, settings.multiplex.inventory.config.links[id]);
							}
						}

					}, 10);
				}
			}
			if (context == document && settings.multiplex.tips.config.enabled !== false) {
				let tips = new TipManager(document.getElementById('page-wrapper'));
				tips.attach(document.body);

				fetch(settings.multiplex.tips.config.apiEndpoint).then(response => response.text()).then(data => {
					tips.addInstructions(JSON.parse(data).data.instructions);
				});
			}
			if (context == document && settings.multiplex.countdown.enabled !== false) {
				let counter = new CountdownDisplay(settings.multiplex.countdown.startTime, "/to" + settings.multiplex.countdown.target, settings.multiplex.countdown.openInNewWindow, settings.multiplex.countdown.targetURL);

				// Give the document a second to load so our target DIV exists
				setTimeout(() => {
					counter.attach(document.body);
				}, 100);
			}
			// Attach the map link (if they have a session)
			if (context == document && settings.multiplex.map.config.mapButtonEnabled == true) {
				let sessionCookie = SpookyMap.getCookies()[settings.multiplex.map.config.sessionCookieName];
				if (sessionCookie != null && sessionCookie != "") {
					let button = SpookyMap.getMapButton(settings.multiplex.map.config.openMapInNewWindow, settings.multiplex.map.config.url);
					document.body.appendChild(button);
				}
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
					nightMode: settings.multiplex.map.config.nightMode,
					backgroundImage: settings.multiplex.map.config.backgroundImage,		// A repeating background image, or one that is the exact size of the map
					linkBaseURL: settings.multiplex.map.config.linkPrefix,				// The URL to prefix codes with when creating links
					altBaseURL: settings.multiplex.map.config.altLinkPrefix,		// The URL to prefix if shift is held down when clicking on a link
					iconBaseURL: settings.multiplex.map.config.iconPrefix,		// The URL to prefix icon images with

					centerMapPosition: settings.multiplex.map.config.centerPosition,		// Where to center the map
					defaultZoomLevel: settings.multiplex.map.config.zoomLevel,					// How close to zoom in, the higher the closer to street level it will zoom

					openLinksInNewWindow: settings.multiplex.map.config.openLinksInNewWindow,				// Whether to open links in a new window (true) or redirect the current window (false)
					showUserLocation: settings.multiplex.map.config.showUserLocation,							// Whether to show a pin to indicate where the user currently is (requires GPS)
					animateHintDuration: settings.multiplex.map.config.animateHintDuration
				});

				// Google callback to initialize map contents (in our case, this is when we attach our map)
				window.initMap = () => {
					myMap.attach(document.body);

					// Make an API call to get the location data (must be on the same domain, or have special headers to allow cross-domain requests)
					function updateMap() {
						let query = "";
						if (document.location.href.indexOf("/map/") >= 0) {
							query = "?path=" + document.location.href.substring(document.location.href.indexOf("/map/") + 5);
						}
						fetch(settings.multiplex.map.config.apiEndpoint + query).then(response => response.text()).then(data => {
							let responseData = JSON.parse(data);
							if (responseData.data.recent != null && responseData.data.recent.lat != null && responseData.data.recent.lng != null) {
								myMap.center(responseData.data.recent.lat, responseData.data.recent.lng);
							}
							myMap.setLegend(responseData.data.legend);
							myMap.setLocations(responseData.data.locations);
						});
					};
					// Give the map a moment to load
					setTimeout(() => {
						updateMap();
					}, 500);
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
