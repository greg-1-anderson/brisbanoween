# Inventory
The list of all possible inventory items is defined in the ***getInventoryItems()*** method of ***multiplex.module***.  The method should return a map, where the keys are the inventory item ID's, and the values are a set of configuration properties:

* **url** - the path to the item image (relative to the domain root)
* **alt** - some descriptive text of what the item is, which will show up in the popup dialog if the user clicks on the item and it is not usable.
* **fixed_order** - an integer used to sort inventory items when displaying them, in a fixed order.  You can turn off fixed ordering in the multiplex configuration page, however its probably worth at least defining a fixed order in case you decide to later enable it.  Note that the cookie should contain the items in the order they were acquired, which is also how they will be displayed if fixed ordering is disabled. 

The set of items each user actually has, is defined in a cookie assigned to that user.  The name of that cookie can be configured on the multiplex configuration page under '***Inventory Cookie Name***'.  An example cookie value might be:

	lantern,dagger,baseball

The unix timestamp in milliseconds of the last item the user acquired, should be stored in the cookie referenced by '***Inventory Added Cookie Name***'. An example cookie value might be:

	1603242931614

The mapping of inventory items to links, which is unique per page, can be handled in two ways.  The mapping can be assigned in ***multiplex.module*** in the ***getInventoryConfig()*** method:

    'links' => [
    	'inventory_item_id' => 'page_to_open'
    ]
or, you can assign links to inventory items within the page itself using javascript.  Copy/Paste the following code (also found in ***example_js_for_adding_link_to_inventory.js***) into the page contents to assign one or more links to inventory items.

	// Assign a link to an inventory item, ensuring that the inventory box has initialized, otherwise defering the action.
	function assignLinkToInventory(item_id, url) {
		if (document.readyState === 'complete') {
			if (typeof InventoryBox != "undefined") {
				InventoryBox.setItemLink(item_id, url);
			}
		}
		else {
			window.addEventListener("load", () => {
				assignLinkToInventory(item_id, url);
			});
		}
	}
	assignLinkToInventory("item_id", "link_path");

# Map
The map can be accessed by going to ***/map***.  The API the map will use to download location data, will come from the ***getMapConfig()*** method in ***multiplex.module***.  The API will be polled periodically, on a frequency defined in the multiplex configuration property '***Map Update Frequency***'.  The format of the API response should be a JSON object with the following keys:
* **legend** - (Array) a collection of objects with the following properties
	* **id** - (String) a unique ID for this icon
	* **icon** - (String) the relative path to the image being defined
	* **name** - (String) a descriptive name telling the user what the icon means
* **locations** - (Array) a collection of objects with the following properties
	* **position** - (Array) lat, long (in that order). Example [37.681767, -122.401551].
	* **code** - (String) the code associated with this location
	* **visited** - (Boolean) whether to open the code page when the location is clicked (optional, default = false)
	* **legendId** - (String) the legend item that contains the icon to use for this location (optional, but must supply icon if not provided)
	* **icon** - (String) a relative path to an image to use, which is not referenced in the legend, instead of the legendId. (optional)

Only locations with '***visited***' set to true and that have a '***code***', will be links.  You can specify whether links open in the same window, or in a new window, in the multiplex configuration parameter '***Map Open Links In New Window***'.

In order to support multiple uses for the map integration, you can also load the map with a paramter at ***/map/****.  For example, if you want a special map for administrators, you could link to it at ***/map/admin***.  The map will work exactly the same, but when it calls the API, it will pass a query parameter named "***path***", that will contain the extra path data after /map/.  So in the example, the API would be called as 

	api?path=admin

In this way, the API can return a different set of locations and a different legend.

