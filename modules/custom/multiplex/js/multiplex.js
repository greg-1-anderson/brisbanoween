function Multiplex_static_content_dirs() {
  $dir = drupal_get_path('module', 'Multiplex') . '/images';
  return array(
    $dir => array()
  );
}

(function ($, Drupal) {
  Drupal.behaviors.myModuleBehavior = {
    attach: function (context, settings) {
      let elem = document.createElement('DIV');
      elem.innerHTML = "Hello world 3";
      $('body').append(elem);
    }
  };
})(jQuery, Drupal);
