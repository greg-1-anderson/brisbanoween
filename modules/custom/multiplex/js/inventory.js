/**
 *	DialogWidget
 *	This class provdies an overlay widget for displaying detailed information about an item in inventory
 */
class DialogWidget {
	/**
	 *	Update the state of the dialog for the new item to display
	 *
	 *	@param {String} text the description of the item
	 *	@param {String} imageURL the URL of the item image
	 *	@param {Integer} width The width of the item image
	 *	@param {Integer} height The height of the item image
	 */
	setContent(text, imageURL, width, height) {
		// Update the state
		this.i_text = text;
		this.i_url = imageURL;
		this.i_width = width;
		this.i_height = height;

		// Re-render
		this.update();
	}

	/**
	 *	Update the dialog DOM elements to reflect the current state of the dialog
	 *
	 *	@private
	 */
	update() {
		// Make sure the element was created already, or there would be nothing to do
		if (this.i_element) {
			this.i_text_container.innerHTML = this.i_text;
			this.i_item.src = this.i_url;
			this.i_item.width = this.i_width;
			this.i_item.height = this.i_height;
		}
	}

	/**
	 *	Handle when the user closes the dialog
	 *
	 *	@private
	 */
	close() {
		// Make sure there is something to close
		if (this.i_element) {
			// Remove it from the DOM
			try {
				document.body.removeChild(this.i_element);
			}
			catch (e) {
				// If it wasnt on the DOM, its not clear how the user clicked the close button, so this should never happen
				console.warn("couldnt remove dialog", e);
			}
		}
	}

	/**
	 *	Get the DOM element that makes up this dialog
	 *
	 *	@return {DOMElement} the element that makes up this dialog
	 */
	getElement() {
		// See if we've created the DOM for the dialog yet
		if (this.i_element == null) {
			// We have not, so create a container now
			this.i_element = document.createElement('DIV');
			this.i_element.className = "DialogWidget";

				// Create a DIV for the contents
				this.i_body = document.createElement('DIV');
				this.i_body.className = "DialogWidget_body";
				this.i_element.appendChild(this.i_body);

					// Add the item image element
					this.i_item = document.createElement('IMG');
					this.i_item.className = "DialogWidget_item";
					this.i_body.appendChild(this.i_item);

					// Create a DIV for the item text
					this.i_text_container = document.createElement('DIV');
					this.i_text_container.className = "DialogWidget_text";
					this.i_body.appendChild(this.i_text_container);

					// Create a DIV for the message about not being able to use it
					this.i_no_use_container = document.createElement('DIV');
					this.i_no_use_container.className = "DialogWidget_no_use_text";
					this.i_no_use_container.innerHTML = "You cannot use this item right now";
					this.i_body.appendChild(this.i_no_use_container);

					// Add a button to close
					this.i_close_button = document.createElement('BUTTON');
					this.i_close_button.className = "DialogWidget_close";
					this.i_close_button.innerHTML = "Close";
					this.i_close_button.addEventListener("click", () => {
						this.close();
					});
					this.i_body.appendChild(this.i_close_button);

			// Update the DOM to reflect our current state
			this.update();
		}
		return this.i_element;
	}
}

/**
 *	InventoryBoxItem
 *	This class represents the display components of a single item in the user's inventory
 *
 *	@private
 */
class InventoryBoxItem {
	/**
	 *	Update the configuration of this item
	 *
	 *	@param {Object} item The item configuration (url and alt properties)
	 *	@param {Boolean} wiggle Whether to wiggle the item
	 *	@param {Integer} width the width of each item in pixels
	 *	@param {Integer} height The height of each item in pixels
	 *	@param {Boolean} openLinksInNewWindow whether to open links in a new window, or on the same page
	 */
	setConfig(item, wiggle, width, height, openLinksInNewWindow) {
		// Update state
		this.i_url = item.url;
		this.i_alt = item.alt;
		this.i_link = item.link;
		this.i_openLinksInNewWindow = openLinksInNewWindow;
		this.i_wiggle = wiggle;
		this.i_width = width;
		this.i_height = height;

		// re-render
		this.update();
	}

	/**
	 *	Handle when the user clicks on an item in inventory
	 *
	 *	@private
	 */
	openDialog() {
		// See if we have a single global dialog component created yet
		if (InventoryBoxItem.i_dialog == null) {
			// We don't, so create one now
			InventoryBoxItem.i_dialog = new DialogWidget();
		}
		else {
			// We did, and its probably attached to the DOM still, so remove it
			try {
				document.body.removeChild(InventoryBoxItem.i_dialog.getElement());
			}
			catch (e) {
				// It may fail, but thats ok, that just means it wasnt attached
			}
		}

		// Update the dialog with the current state of this item
		InventoryBoxItem.i_dialog.setContent(this.i_alt, this.i_url, this.i_width, this.i_height);

		// Add the dialog to the DOM
		document.body.appendChild(InventoryBoxItem.i_dialog.getElement());
	}

	/**
	 *	Update the visual elements of this item based on updated state
	 *
	 *	@private
	 */
	update() {
		// Make sure we have a DOM element, or updating wouldnt do anything
		if (this.i_element != null) {
			this.i_element.className = "InventoryBoxItem" + (this.i_wiggle ? " InventoryBoxItem_wiggle" : "");
			this.i_element.src = this.i_url ? this.i_url : "";
			this.i_element.width = this.i_width;
			this.i_element.height = this.i_height;
			this.i_element.title = this.i_alt ? this.i_alt : "";
			this.i_element.alt = this.i_alt ? this.i_alt : "";
		}
	}

	/**
	 *	Get the DOM element that makes up this item
	 *
	 *	@return {DOMElement} the element that makes up this item
	 */
	getElement() {
		// See if we've created the item yet
		if (this.i_element == null) {
			// We havent, so create it
			this.i_element = document.createElement('IMG');
			this.i_element.addEventListener("click", () => {
				if (this.i_link != null) {
					if (this.i_openLinksInNewWindow) {
						window.open(this.i_link);
					}
					else {
						document.location = this.i_link;
					}
				}
				else {
					this.openDialog();
				}
			});

			// Update it to reflect our initial state
			this.update();
		}

		// Return the newly created item
		return this.i_element;
	}
}

/**
 *	InventoryBox
 *	This widget is designed to maintain a menu of inventory items, identified by a cookie
 */
class InventoryBox {
	/**
	 *	Create a new instance of an inventory box
	 *
	 *	@param {Object} config The JSON data that defines how the menu should work
	 *	@param {Object} items A JSON map of item ID's to their configurations
	 */
	constructor(config, items) {
		this.i_config = config;
		this.i_items = items;

		this.i_item_cache = [];	// Array that will contain DOM elements for each item
	}

	/**
	 *	Get a map of cookie values
	 *
	 *	@private
	 *
	 *	@return {Object} a map of all the cookies
	 */
	getCookies() {
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

	/**
	 *	Get the list of item ID's in the user's current inventory
	 *
	 *	@private
	 *
	 *	@return {String[]} an array of object ID's, empty if no items in inventory
	 */
	getInventory() {
		// Get all the cookies
		let cookies = this.getCookies();
		console.log(cookies);

		// Parse out the inventory cookie if we have one
		let currentInventory = [];
		if (cookies[this.i_config.cookie_name] != null) {
			currentInventory = cookies[this.i_config.cookie_name].split(",");
		}

		// Filter out any invalid items
		for (let x = currentInventory.length - 1; x >= 0; x--) {
			if (currentInventory[x] == '') {
				// The cookie was probably empty, so delete the empty string, which will result in an empty array.
				currentInventory.splice(x, 1);
			}
			else if (this.i_items[currentInventory[x]] == null) {
				// Item not found, delete it from the array
				console.warn("Cookie contains an unknown inventory item (ignoring): ", currentInventory[x]);
				currentInventory.splice(x, 1);
			}
		}

		// Return the list of inventory items
		return currentInventory;
	}

	/**
	 *	Get the date the last item was added to inventory (if its not empty)
	 *
	 *	@private
	 *
	 *	@return {Date} the date the last item was added (or null if the inventory is empty)
	 */
	getLastIssuedItem() {
		let lastIssued = null;

		// Find the last item added cookie
		let cookies = this.getCookies();
		if (cookies[this.i_config.last_item_added_cookie_name] != null) {
			let lastIssuedTimestamp = parseInt(cookies[this.i_config.last_item_added_cookie_name]);
			if (lastIssuedTimestamp > 0) {
				// Parse the date stored in the cookie and return it
				lastIssued = new Date();
				lastIssued.setTime(lastIssuedTimestamp);
			}
		}
		return lastIssued;
	}

	/**
	 *	Remove an item from inventory by its ID
	 *
	 *	@param {String} id the ID to remove
	 *
	 *	@return {Boolean} true if the item was found and removed, false if it wasnt in the inventory to begin with
	 */
	removeItemFromInventory(id) {
		// Get the current list of items
		let currentInventory = this.getInventory();

		// See if we're about to remove the last item
		let isLastItem = (currentInventory.length > 0 && currentInventory[currentInventory.length - 1] == id);

		// Remove the item
		let foundItem = false;
		for (let x = 0; x < currentInventory.length; x++) {
			if (currentInventory[x] == id) {
				currentInventory.splice(x, 1);
				foundItem = true;
				break;
			}
		}

		// If we're removing the last item, stop wiggling it (or we would end up wiggling the previous item)
		if (isLastItem) {
			document.cookie = this.i_config.last_item_added_cookie_name + "=0; path=/; expires=Fri, 31 Dec 9999 23:59:59 GMT";
		}

		// Update the cookie with the new inventory
		document.cookie = this.i_config.cookie_name + "=" + encodeURIComponent(currentInventory.join(",")) + "; path=/; expires=Fri, 31 Dec 9999 23:59:59 GMT";

		// If we're rendering a widget, update the inventory now
		this.updateInventoryWidget();

		return foundItem;
	}

	/**
	 *	Add a new item to inventory by its ID
	 *
	 *	@param {String} id the ID to add
	 */
	addItemToInventory(id) {
		// Get the current list of items
		let currentInventory = this.getInventory();

		// Remove the one we're about to add if they already had it (so it'll end up at the end of the list)
		for (let x = 0; x < currentInventory.length; x++) {
			if (currentInventory[x] == id) {
				currentInventory.splice(x, 1);
				break;
			}
		}

		// Add the new item
		currentInventory.push(id);

		// Update the cookie with the new inventory
		console.log(this.i_config.cookie_name + "=" + encodeURIComponent(currentInventory.join(",")) + "; path=/; expires=Fri, 31 Dec 9999 23:59:59 GMT");
		document.cookie = this.i_config.cookie_name + "=" + encodeURIComponent(currentInventory.join(",")) + "; path=/; expires=Fri, 31 Dec 9999 23:59:59 GMT";

		// Update the last added timestamp so we know how long to wiggle for
		document.cookie = this.i_config.last_item_added_cookie_name + "=" + (new Date()).getTime() + "; path=/; expires=Fri, 31 Dec 9999 23:59:59 GMT";

		// If we're rendering a widget, update the inventory now
		this.updateInventoryWidget();
	}

	/**
	 *	Change the open/closed state of the widget
	 *
	 *	@param {Boolean} state True to show the full bar, false to show only the minimal button
	 */
	setVisible(state) {
		// Update the visible state
		this.i_visible = state;

		// Re-render
		this.updateInventoryWidget();
	}

	/**
	 *	Update the DOM to reflect the current state of the component
	 *
	 *	@private
	 */
	updateInventoryWidget() {
		// Make sure we've build the widget, otherwise theres no point doing anything yet
		if (this.i_element != null) {
			// Get the user's inventory
			let currentInventory = this.getInventory();
			console.log(currentInventory);

			// Update if we're showing the inventory right now
			this.i_bar.style.display = ((this.i_visible !== false && currentInventory.length > 0) ? "grid" : "none");
			this.i_show_wrapper.style.display = ((this.i_visible !== false || currentInventory.length == 0) ? "none" : "");

			// See if the last item was added recently enough for the wiggle animation
			let lastItemAdded = this.getLastIssuedItem(), wiggleId = null;
			if (this.i_config.wiggle_duration_ms > 0 && lastItemAdded != null) {
				let deltaWiggle = (new Date()).getTime() - lastItemAdded.getTime();
				if (deltaWiggle < this.i_config.wiggle_duration_ms) {
					// We need to wiggle
					wiggleId = currentInventory[currentInventory.length - 1];

					// Setup a timer to re-render the widget when the wiggle animation should stop
					if (this.i_recheck_wiggle != null) {
						clearTimeout(this.i_recheck_wiggle);
					}
					this.i_recheck_wiggle = setTimeout(() => {
						this.i_recheck_wiggle = null;
						this.updateInventoryWidget();
					}, (this.i_config.wiggle_duration_ms - deltaWiggle) + 100);
				}
			}



			// See if we need to sort the list
			if (this.i_config.use_fixed_order) {
				// Sort the inventory list to match the fixed order
				currentInventory.sort((a,b) => {
					return this.i_items[a].fixed_order < this.i_items[b].fixed_order ? -1 : this.i_items[a].fixed_order > this.i_items[b].fixed_order ? 1 : 0;
				});
			}

			// Loop over all the items they currently have
			let x = 0;
			for (x; x < currentInventory.length; x++) {
				// Create a new icon container if we dont already have enough from a previous render
				if (this.i_item_cache[x] == null) {
					this.i_item_cache[x] = new InventoryBoxItem();
				}

				// Update the icon container with the current inventory item data
				this.i_item_cache[x].setConfig(this.i_items[currentInventory[x]], currentInventory[x] == wiggleId, this.i_config.image_width, this.i_config.image_height, this.i_config.openLinksInNewWindow);

				// Attach it to the DOM if its not already
				if (this.i_item_cache[x].i_attached != true) {
					this.i_images.appendChild(this.i_item_cache[x].getElement());
					this.i_item_cache[x].i_attached = true;
				}
			}
			// Hide any unused containers from a previous render
			for (x; x < this.i_item_cache.length; x++) {
				if (this.i_item_cache[x].i_attached) {
					this.i_images.removeChild(this.i_item_cache[x].getElement());
					this.i_item_cache[x].i_attached = false;
				}
			}

		}
	}

	/**
	 *	Attach this component to a specified DOM container
	 *
	 *	@param {DOMElement} container the HTML element to attach to
	 */
	attach(container) {
		// See if we've build the inventory widget yet
		if (this.i_element == null) {
			// We have not, so build it
			this.i_element = document.createElement('DIV');
			this.i_element.className = "InventoryBox";

				// Create a container for the visible inventory bar
				this.i_bar = document.createElement('DIV');
				this.i_bar.className = "InventoryBox_bar";
				this.i_element.appendChild(this.i_bar);

						// Container for the item images
						this.i_images = document.createElement('DIV');
						this.i_images.className = "InventoryBox_images";
						this.i_bar.appendChild(this.i_images);

						// Container for the close button
						this.i_hide_wrapper = document.createElement('DIV');
						this.i_hide_wrapper.className = "InventoryBox_hide_wrapper";
						this.i_hide_wrapper.addEventListener("click", () => {
							this.setVisible(false);
						});
						this.i_bar.appendChild(this.i_hide_wrapper);

							// The close button icon
							this.i_hide_image = document.createElement('DIV');
							this.i_hide_image.className = "InventoryBox_hide_image";
							this.i_hide_wrapper.appendChild(this.i_hide_image);

							// The close button label
							this.i_hide_arrow = document.createElement('DIV');
							this.i_hide_arrow.className = "InventoryBox_hide_arrow";
							this.i_hide_arrow.innerHTML = "[HIDE]";
							this.i_hide_wrapper.appendChild(this.i_hide_arrow);

				// Create a container for the button we show when the bar is closed (to reopen it)
				this.i_show_wrapper = document.createElement('DIV');
				this.i_show_wrapper.className = "InventoryBox_show_wrapper";
				this.i_show_wrapper.addEventListener("click", () => {
					this.setVisible(true);
				});
				this.i_element.appendChild(this.i_show_wrapper);

					// Container for the open button icon
					this.i_show_image = document.createElement('DIV');
					this.i_show_image.className = "InventoryBox_show_image";
					this.i_show_wrapper.appendChild(this.i_show_image);

					// Container for the open button text
					this.i_show_arrow = document.createElement('DIV');
					this.i_show_arrow.className = "InventoryBox_show_arrow";
					this.i_show_arrow.innerHTML = "[SHOW]";
					this.i_show_wrapper.appendChild(this.i_show_arrow);

			// Populate the widget with the user's inventory
			this.updateInventoryWidget();
		}
		else {
			// We have, which means we probably attached it somewhere else, so remove it from where ever that is
			if (this.i_element.parent != container) {
				this.i_element.parent.removeChild(this.i_element);
			}
		}

		// Attach the widget to the container
		container.appendChild(this.i_element);
	}
}