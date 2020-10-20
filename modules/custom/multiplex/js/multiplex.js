(function ($, Drupal) {
  Drupal.behaviors.myModuleBehavior = {
    attach: function (context, settings) {
      console.log(context, settings);

      // Create the inventory widget
      let inventoryBox = new InventoryBox(
      // Configuration
        {
          'cookie_name': 'myInventory',							// The name of the cookie to store the inventory in
          'last_item_added_cookie_name': 'myInventory_added',		// The name of the cookie to store hte last added timestamp in
          'wiggle_duration_ms': 120000,							// How many milliseconds after aquiring an item should it wiggle (0 = disabled)
          'use_fixed_order':true,									// Visibly display items in a fixed order.  set to false to ignore "fixed_order" property on each item
          'image_width': 72,
          'image_height': 72,
        },

        // Map of items
        {
          'lantern':{
            'url':'/modules/custom/multiplex/images/objects/lantern.png',
            'alt':'The green spooky lantern of magic',
            'fixed_order': 1,
          },
          'pumpkin':{
            'url':'/modules/custom/multiplex/images/objects/pumpkin.png',
            'alt':'The enchanted pumpkin',
            'fixed_order': 2,
          },
          'top_hat':{
            'url':'/modules/custom/multiplex/images/objects/top_hat.png',
            'alt':'A musty old hat',
            'fixed_order': 3,
          },
          'old_key':{
            'url':'/modules/custom/multiplex/images/objects/old_key.png',
            'alt':'A mysterious key, I wonder what it goes to',
            'fixed_order': 4,
          },
          // Add as many as you need in the same format.
        }
      );

      // Attach it to the page (give it a second, so we drop below the privacy policy - this is a hack)
      setTimeout(() => {
      	inventoryBox.attach(document.body);
      }, 10);
    }
  };
})(jQuery, Drupal);
