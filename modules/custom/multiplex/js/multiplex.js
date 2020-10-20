(function ($, Drupal) {
  Drupal.behaviors.myModuleBehavior = {
    attach: function (context, settings) {
    	console.log(context, settings);
      if (context == document) {
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
    }
  };
})(jQuery, Drupal);
