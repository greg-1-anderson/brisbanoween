(function ($, Drupal) {
  Drupal.behaviors.myModuleBehavior = {
    attach: function (context, settings) {
    	objects = visitorCookieGetValue('STYXKEY_objects')
      if (objects) {
        $('body').append('<div style="background-color: orange; position: fixed; bottom: 0px;"><h1>Inventory: ' + objects + '</h1></div>')      	
      }
    }
  };
})(jQuery, Drupal);
