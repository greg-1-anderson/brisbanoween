(function ($, Drupal) {
  Drupal.behaviors.myModuleBehavior = {
    attach: function (context, settings) {
      let elem = document.createElement('DIV');
      elem.innerHTML = "Hello world 3";
      $('body').append(elem);
    }
  };
})(jQuery, Drupal);
