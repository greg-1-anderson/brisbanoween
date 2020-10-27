class SpookyMapLegendItem {
	setName(name) {
		this.i_name = name;
		this.update();
	}

	setIcon(url) {
		this.i_icon = url;
		this.update();
	}

	update() {
		if (this.i_element != null) {
			this.i_icon_img.src = this.i_icon;
			this.i_label.innerHTML = this.i_name;
		}
	}

	getElement() {
		if (this.i_element == null) {
			this.i_element = document.createElement('DIV');
			this.i_element.className = "SpookyMapLegendItem";

				this.i_icon_img = document.createElement('IMG');
				this.i_icon_img.className = "SpookyMapLegendItem_image";
				this.i_element.appendChild(this.i_icon_img);

				this.i_label = document.createElement('DIV');
				this.i_label.className = "SpookyMapLegendItem_label";
				this.i_element.appendChild(this.i_label);

			this.update();
		}
		return this.i_element;
	}
}

/**
 *	SpookyMap
 *	This component creates a google map with spooky undertones.  It pins visited and unvisited locations that are
 *	provided to it from an outside caller.  Visited locations link to specific pages.
 */
class SpookyMap {
	/**
	 *	Create a new map
	 *
	 *	@param {Object} config the config object
	 */
	constructor(config) {
		this.i_config = config;

		this.i_marker_cache = [];
		this.i_legend_cache = [];
	}

	static getCookies() {
		// Get the document's cookies
		let existingCookies = document.cookie, cookies = {};
		if (existingCookies) {
			// Parse the cookies into each name/value pair
			existingCookies = existingCookies.split(";");

			// For each pair, map the cookies name to it's value in the cookies collection
			for (let x = 0; x < existingCookies.length; x++) {
				let cookieParts = existingCookies[x].trim().split("=");
				cookies[cookieParts.shift()] = decodeURIComponent(cookieParts.join("="));
			}
		}

		// Return the cookies collection
		return cookies;
	}

	static getMapButton(openLinksInNewWindow, url) {
		let button = document.createElement('DIV');
		button.className = "SpookyMap_link";
		button.addEventListener("click", (e) => {
			if (openLinksInNewWindow) {
				window.open(url);
			}
			else {
				document.location = url;
			}
			e.cancelBubble = true;
			e.preventDefault();
		});
		return button
	}

	/**
	 *	Update the legend
	 *
	 *	@param {Object[]} legendItems the array of legend data
	 */
	setLegend(legendItems) {
		this.i_legend = legendItems;
		this.updateLegend();
	}


	/**
	 *	Update the list of locations and their visited states
	 *
	 *	@param {Object[]} newLocations the array of location data
	 */
	setLocations(newLocations) {
		this.i_locations = newLocations;
		this.updateLocations();
	}

	/**
	 *	Update the items in the legend
	 */
	updateLegend() {
		if (this.i_map != null && this.i_legend != null) {
			let x = 0;
			for (x; x < this.i_legend.length; x++) {
				if (this.i_legend_cache[x] == null) {
					this.i_legend_cache[x] = new SpookyMapLegendItem();
				}
				this.i_legend_cache[x].setName(this.i_legend[x].name);
				this.i_legend_cache[x].setIcon(this.i_config.iconBaseURL + this.i_legend[x].icon);

				if (this.i_legend_cache[x].i_attached != true) {
					this.i_legend_box.appendChild(this.i_legend_cache[x].getElement());
					this.i_legend_cache[x].i_attached = true;
				}
			}
			for (x; x < this.i_legend_cache.length; x++) {
				if (this.i_legend_cache[x].i_attached) {
					this.i_legend_cache[x].i_attached = false;
					this.i_legend_box.removeChild(this.i_legend_cache[x].getElement());
				}
			}
		}
	}

	/**
	 *	Update the markers on the map
	 *
	 *	@private
	 */
	updateLocations() {
		// Make sure we have a map
		if (this.i_map != null) {
			// Get the locations
			let locations = this.i_locations ? this.i_locations : [];
			let legend = this.i_legend ? this.i_legend : [];
			let legendMap = {};
			for (let x = 0; x < legend.length; x++) {
				legendMap[legend[x].id] = legend[x];
			}


			// For each visible locations
			let x = 0;
			let lastBounceEnds = 0;
			for (x; x < locations.length; x++) {
				let remBounceTime = ((new Date()).getTime() - locations[x].time);
				let shouldBounce = locations[x].time > 0 && remBounceTime < this.i_config.animateHintDuration;
				if (shouldBounce && remBounceTime > lastBounceEnds) {
					lastBounceEnds = remBounceTime;
				}

				// See if we have a marker to use for it from a previous update
				if (this.i_marker_cache[x] == null) {
					// We do not, so create one now
					this.i_marker_cache[x] = {};
					this.i_marker_cache[x].marker = new google.maps.Marker({
						position: new google.maps.LatLng(parseFloat(locations[x].position[0]), parseFloat(locations[x].position[1])),
						map: this.i_map,
						animation: shouldBounce ? google.maps.Animation.BOUNCE : null,
						icon: locations[x].icon ? this.i_config.iconBaseURL + locations[x].icon : (legendMap[locations[x].legendId] ? this.i_config.iconBaseURL + legendMap[locations[x].legendId].icon : null)
					});

					// Setup the click handler to redirect the browser (or open a window)
					let locationIndex = x;
					this.i_marker_cache[x].marker.addListener("click", () => {
						if (locations[locationIndex].visited == true) {
							if (this.i_config.openLinksInNewWindow) {
								window.open(this.i_config.linkBaseURL + locations[locationIndex].code);
							}
							else {
								document.location = this.i_config.linkBaseURL + locations[locationIndex].code;
							}
						}
					});
				}
				else {
					// We already had a marker, so move it to the new location
					this.i_marker_cache[x].marker.setPosition(new google.maps.LatLng(parseFloat(locations[x].position[0]), parseFloat(locations[x].position[1])));
					this.i_marker_cache[x].marker.setIcon(locations[x].icon ? this.i_config.iconBaseURL + locations[x].icon : (legendMap[locations[x].legendId] ? this.i_config.iconBaseURL + legendMap[locations[x].legendId].icon : null));
					this.i_marker_cache[x].marker.setAnimation(shouldBounce ? google.maps.Animation.BOUNCE : null);
					this.i_marker_cache[x].marker.setMap(this.i_map);
				}
			}

			if (this.i_refresh_timer != null) {
				clearTimeout(this.i_refresh_timer);
				this.i_refresh_timer = null;
			}
			if (lastBounceEnds > 0) {
				setTimeout(() => {
					this.updateLocations();
				}, lastBounceEnds + 100);
			}

			// Remove any unused markers from previous renders
			for (x; x < this.i_marker_cache.length; x++) {
				this.i_marker_cache[x].setMap(null);
			}
		}
	}

	/**
	 *	Update the marker on the map that reflects where the user actually is
	 */
	updateMyPosition(position) {
		if (this.i_my_marker == null) {
				this.i_my_marker = new google.maps.Marker({
					position: this.i_config.centerMapPosition,
					map: this.i_map,
					icon: this.i_config.iconBaseURL + "you_are_here.png"
				});
		}
		let usePos = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
		this.i_my_marker.setPosition(usePos);
	}

	/**
	 *	Attach the map to the DOM
	 *
	 *	@param {DOMElement} container the container to add the map to
	 */
	attach(container) {
		// See if we have an element yet
		if (this.i_element == null) {
			// We dont, so create it now
			this.i_element = document.createElement('DIV');
			this.i_element.style.width = this.i_config.width;
			this.i_element.style.height = this.i_config.height;
			this.i_element.style.visibility = "hidden";
			this.i_element.className = "SpookyMap";

				// The control bar
				this.i_controls = document.createElement('DIV');
				this.i_controls.className = "SpookyMap_controls";
				this.i_element.appendChild(this.i_controls);

					// The back button
					this.i_back_button = document.createElement('BUTTON');
					this.i_back_button.className = "SpookyMap_button";
					this.i_back_button.addEventListener("click", () => {
						history.go(-1);
					});
					this.i_controls.appendChild(this.i_back_button);

					// Legend
					this.i_legend_box = document.createElement('DIV');
					this.i_legend_box.className = "SpookyMap_legend";
					this.i_controls.appendChild(this.i_legend_box);

				// Wrapper that contains the background for the map
				this.i_map_wrapper = document.createElement('DIV');
				this.i_map_wrapper.className = "SpookyMap_wrapper";
				this.i_map_wrapper.style.backgroundImage = "url(" + this.i_config.backgroundImage + ")";
				this.i_element.appendChild(this.i_map_wrapper);

					// Wrapper that contains the map and is semi transparent to allow the background to bleed through
					this.i_map_container = document.createElement('DIV');
					this.i_map_container.className = "SpookyMap_container";
					this.i_map_container.style.opacity = this.i_config.mapOpacity;
					this.i_map_wrapper.appendChild(this.i_map_container);
		}
		else {
			// We already had the element, so make sure its not attached to the DOM yet
			try {
				this.i_element.parent.removeChild(this.i_element);
			}
			catch (e) { }
		}

		// Attach the element to the DOM
		container.appendChild(this.i_element);

		// Create the map if we dont already have one
		if (this.i_map == null) {
			this.i_map = new google.maps.Map(this.i_map_container, {
				center: this.i_config.centerMapPosition,
				zoom: this.i_config.defaultZoomLevel,
				mapTypeId: this.i_config.defaultStreetMapType ? 'roadmap' : 'satellite',
				mapTypeControl: this.i_config.allowMapTypeToggle,
				streetViewControl: this.i_config.allowStreetView,
				backgroundColor: this.i_config.nightMode ? '#000000' : '#FFFFFF',
				zoomControlOptions: {
		   		position: google.maps.ControlPosition.LEFT_TOP,
				}
			});

			// Dont show points of interest or transit stops
			let styles = [];
			if (this.i_config.nightMode) {
				styles = [
					{ elementType: "geometry", stylers: [{ color: "#242f3e" }] },
					{ elementType: "labels.text.stroke", stylers: [{ color: "#242f3e" }] },
					{ elementType: "labels.text.fill", stylers: [{ color: "#746855" }] },
					{
						featureType: "administrative.locality",
						elementType: "labels.text.fill",
						stylers: [{ color: "#d59563" }],
					},
					{
						featureType: "poi",
						elementType: "labels.text.fill",
						stylers: [{ color: "#d59563" }],
					},
					{
						featureType: "poi.park",
						elementType: "geometry",
						stylers: [{ color: "#263c3f" }],
					},
					{
						featureType: "poi.park",
						elementType: "labels.text.fill",
						stylers: [{ color: "#6b9a76" }],
					},
					{
						featureType: "road",
						elementType: "geometry",
						stylers: [{ color: "#38414e" }],
					},
					{
						featureType: "road",
						elementType: "geometry.stroke",
						stylers: [{ color: "#212a37" }],
					},
					{
						featureType: "road",
						elementType: "labels.text.fill",
						stylers: [{ color: "#9ca5b3" }],
					},
					{
						featureType: "road.highway",
						elementType: "geometry",
						stylers: [{ color: "#746855" }],
					},
					{
						featureType: "road.highway",
						elementType: "geometry.stroke",
						stylers: [{ color: "#1f2835" }],
					},
					{
						featureType: "road.highway",
						elementType: "labels.text.fill",
						stylers: [{ color: "#f3d19c" }],
					},
					{
						featureType: "transit",
						elementType: "geometry",
						stylers: [{ color: "#2f3948" }],
					},
					{
						featureType: "transit.station",
						elementType: "labels.text.fill",
						stylers: [{ color: "#d59563" }],
					},
					{
						featureType: "water",
						elementType: "geometry",
						stylers: [{ color: "#17263c" }],
					},
					{
						featureType: "water",
						elementType: "labels.text.fill",
						stylers: [{ color: "#515c6d" }],
					},
					{
						featureType: "water",
						elementType: "labels.text.stroke",
						stylers: [{ color: "#17263c" }],
					}
				];
			}
			styles.push({
				featureType: "poi",
				elementType: "labels.icon",
				stylers: [{ visibility: "off" }],
			});
			styles.push({
				featureType: "transit",
				elementType: "labels.icon",
				stylers: [{ visibility: "off" }],
			});
			styles.push({
				featureType: "administrative",
				elementType: "labels.text",
				stylers: [{ visibility: 'off' }],
			});
			this.i_map.setOptions({styles: styles});

			setTimeout(() => {
				this.i_element.style.visibility = "visible";
			}, 100);

			// Attempt to track the user so we can put a pin on the map showing where they are
			if (this.i_config.showUserLocation) {
				try {
					this.i_my_watcher = navigator.geolocation.watchPosition((p) => {
						this.updateMyPosition(p);
					}, (err) => {
						console.error("Error getting user's location: ", err);
					},
						{'enableHighAccuracy':true}	// Use GPS if we have it
					);
				}
				catch (err) {
					console.warn("User location is not available in this environment", err);
				}
			}

			// Update the map location set if we have it already
			this.updateLocations();
			this.updateLegend();
		}
	}
}