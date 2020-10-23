<?php

namespace Drupal\multiplex\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Configure Multiplex settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'multiplex_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['multiplex.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // TODO: Inject service into form
    $moduleHandler = \Drupal::service('module_handler');
    $has_guest_upload_module = $moduleHandler->moduleExists('guest_upload');

    $cookie_value = $this->config('multiplex.settings')->get('cookie');
    if ($has_guest_upload_module) {
      $cookie_value = $this->config('guest_upload.settings')->get('cookie');
    }

	$currentStartTime = $this->config('multiplex.settings')->get('game_start_time') ? DrupalDateTime::createFromTimestamp(intval($this->config('multiplex.settings')->get('game_start_time'))) : DrupalDateTime();
    $form['game_start_time'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Game Start Time'),
      '#description' => $this->t("When does the game officially begin?"),
      '#default_value' => $currentStartTime
    ];
    $form['cookie'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cookie'),
      '#description' => $this->t("ID of cookie that identifies visitor's identity. If guest_upload module is enabled, its cookie will always be used."),
      '#default_value' => $cookie_value,
      '#disabled' => $has_guest_upload_module,
    ];
    $form['unidentified_user_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Unidentified User Path'),
      '#description' => $this->t("Page to redirect to if an unidentified visitor (no cookie set) goes to a random multiplex path. If empty, will pass through."),
      '#default_value' => $this->config('multiplex.settings')->get('unidentified_user_path'),
    ];

    $form['map_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Map API Key'),
      '#description' => $this->t("Google Maps API enabled API key.  You will need to get this from the google credential management interface, and will need to whitelist the domain name you plan to use it on."),
      '#default_value' => $this->config('multiplex.settings')->get('map_api_key') ? $this->config('multiplex.settings')->get('map_api_key') : ''
    ];
    $form['map_update_frequency'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Map Update Frequency'),
      '#description' => $this->t("How many seconds between checks for map updates.  (0 = only check once on page load)"),
      '#default_value' => $this->config('multiplex.settings')->get('map_update_frequency') ? $this->config('multiplex.settings')->get('map_update_frequency') : 0
    ];
    $form['map_link_prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Map Base Link'),
      '#description' => $this->t("The URL to open when the user clicks on a visited map icon.  The code for the icon will be appended"),
      '#default_value' => $this->config('multiplex.settings')->get('map_link_prefix') ? $this->config('multiplex.settings')->get('map_link_prefix') : 'https://g1a.io/'
    ];
    $form['map_center_lat'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Map Center Latitude'),
      '#description' => $this->t("Center the map on this latitude"),
      '#default_value' => $this->config('multiplex.settings')->get('map_center_lat') ? $this->config('multiplex.settings')->get('map_center_lat') : 37.681275
    ];
    $form['map_center_lng'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Map Center Longitude'),
      '#description' => $this->t("Center the map on this longitude"),
      '#default_value' => $this->config('multiplex.settings')->get('map_center_lng') ? $this->config('multiplex.settings')->get('map_center_lng') : -122.401968
    ];
    $form['map_default_zoom'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Map Default Zoom'),
      '#description' => $this->t("The default zoom level for the map.  14 is about the size of Brisbane on a phone screen.  Larger numbers zoom furhter in."),
      '#default_value' => $this->config('multiplex.settings')->get('map_default_zoom') ? $this->config('multiplex.settings')->get('map_default_zoom') : 15.3
    ];
    $form['map_open_links_in_new_window'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Map Open Links In New Window'),
      '#description' => $this->t("Show inventory in a fixed display order, instead of by order of aquisition"),
      '#default_value' => $this->config('multiplex.settings')->get('map_open_links_in_new_window') ? $this->config('multiplex.settings')->get('map_open_links_in_new_window') : false
    ];

    $form['map_allow_type_toggle'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Map Allow Map Type Switching'),
      '#description' => $this->t("Allow the user to switch between sattalite and roadmap types"),
      '#default_value' => $this->config('multiplex.settings')->get('map_allow_type_toggle') ? $this->config('multiplex.settings')->get('map_allow_type_toggle') : false
    ];

    $form['map_use_roadmap'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Map Use Roadmap By Default'),
      '#description' => $this->t("Start the map in roadmap view, otherwise start in sattalite view"),
      '#default_value' => $this->config('multiplex.settings')->get('map_use_roadmap') ? $this->config('multiplex.settings')->get('map_use_roadmap') : false
    ];

    $form['map_allow_street_view'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Map Allow Street View'),
      '#description' => $this->t("Allow the user to zoom all the way into street view (where you can virtually walk around)"),
      '#default_value' => $this->config('multiplex.settings')->get('map_allow_street_view') ? $this->config('multiplex.settings')->get('map_allow_street_view') : false
    ];

    $form['map_show_user_location'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Map Show User Location'),
      '#description' => $this->t("Show the user where they are on the map, updated live (only works if the user allows location tracking and they have GPS)"),
      '#default_value' => $this->config('multiplex.settings')->get('map_show_user_location') ? $this->config('multiplex.settings')->get('map_show_user_location') : false
    ];

    $form['map_opacity'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Map Transparency Level'),
      '#description' => $this->t("How opaque should the map be.  The less opaque, the more visible the background pattern will be.  0.8 = 80% visible."),
      '#default_value' => $this->config('multiplex.settings')->get('map_opacity') ? $this->config('multiplex.settings')->get('map_opacity') : 0.9
    ];

    $form['map_night_mode'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Map Night Mode'),
      '#description' => $this->t("Invert the color scheme of the map, making it much darker."),
      '#default_value' => $this->config('multiplex.settings')->get('map_night_mode') ? $this->config('multiplex.settings')->get('map_night_mode') : false
    ];

    $form['inventory_cookie'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Inventory Cookie Name'),
      '#description' => $this->t("ID of cookie that contains the user's inventory."),
      '#default_value' => $this->config('multiplex.settings')->get('inventory_cookie') ? $this->config('multiplex.settings')->get('inventory_cookie') : 'STYXKEY_inventory'
    ];
    $form['inventory_added_cookie'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Inventory Added Cookie Name'),
      '#description' => $this->t("ID of cookie that contains the unix timestamp (in milliseconds) of the last item added to inventory."),
      '#default_value' => $this->config('multiplex.settings')->get('inventory_added_cookie') ? $this->config('multiplex.settings')->get('inventory_added_cookie') : 'STYXKEY_inventory_added'
    ];
    $form['inventory_fixed_order'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Inventory Fixed Display Order'),
      '#description' => $this->t("Show inventory in a fixed display order, instead of by order of aquisition"),
      '#default_value' => $this->config('multiplex.settings')->get('inventory_fixed_order') ? $this->config('multiplex.settings')->get('inventory_fixed_order') : false
    ];
    $form['inventory_links_in_new_window'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Inventory Open Links In New Window'),
      '#description' => $this->t("If an inventory item is usable and is clicked, should a new window open, or should the current window change locations?"),
      '#default_value' => $this->config('multiplex.settings')->get('inventory_links_in_new_window') ? $this->config('multiplex.settings')->get('inventory_links_in_new_window') : false
    ];
    $form['inventory_wiggle_duration'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Inventory New Item Wiggle Duration'),
      '#description' => $this->t("How many milliseconds after an item is aquired, should it wiggle in the inventory panel"),
      '#default_value' => $this->config('multiplex.settings')->get('inventory_wiggle_duration') ? $this->config('multiplex.settings')->get('inventory_wiggle_duration') : '120000'
    ];
    $form['inventory_icon_width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Inventory Icon Width'),
      '#description' => $this->t("How wide should the inventory icons be"),
      '#default_value' => $this->config('multiplex.settings')->get('inventory_icon_width') ? $this->config('multiplex.settings')->get('inventory_icon_width') : '72'
    ];
    $form['inventory_icon_height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Inventory Icon Height'),
      '#description' => $this->t("How high should the inventory icons be"),
      '#default_value' => $this->config('multiplex.settings')->get('inventory_icon_height') ? $this->config('multiplex.settings')->get('inventory_icon_height') : '72'
    ];
    $form['inventory_base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Inventory Base URL'),
      '#description' => $this->t("When an item is clicked in inventory, which has a link associated, this will be its prefix. It should have a trailing slash.  It can also be empty for absolute URLs."),
      '#default_value' => $this->config('multiplex.settings')->get('inventory_base_url') ? $this->config('multiplex.settings')->get('inventory_base_url') : ''
    ];
    $form['inventory_update_frequency'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Inventory Update Frequency'),
      '#description' => $this->t("How many seconds between inventory updates.  (0 = only update on page load)"),
      '#default_value' => $this->config('multiplex.settings')->get('inventory_update_frequency') ? $this->config('multiplex.settings')->get('inventory_update_frequency') : '0'
    ];
    $form['counter_open_in_new_window'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Countdown Timer Opens A New Window'),
      '#description' => $this->t("If the user visits a page before the game starts, they will get a countdown timer.  Once the game begins, should the user be redirected to the original page in the same tab, or should a new window be opened?"),
      '#default_value' => $this->config('multiplex.settings')->get('counter_open_in_new_window') ? $this->config('multiplex.settings')->get('counter_open_in_new_window') : false
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /*
    if ($form_state->getValue('cookie') == 'validation-test') {
      $form_state->setErrorByName('cookie', $this->t('The value is not correct.'));
    }
    */
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  	$form_fields = array(
  		'game_start_time', 'cookie', 'unidentified_user_path', 'inventory_cookie', 'inventory_added_cookie', 'inventory_fixed_order', 'inventory_links_in_new_window',
  		'inventory_wiggle_duration', 'inventory_icon_width', 'inventory_icon_height', 'inventory_update_frequency', 'inventory_base_url', 'map_link_prefix',
  		'map_center_lat', 'map_center_lng', 'map_default_zoom', 'map_open_links_in_new_window', 'map_show_user_location', 'map_api_key', "map_night_mode",
  		'map_allow_type_toggle', 'map_use_roadmap', 'map_allow_street_view', 'map_opacity', 'map_update_frequency', 'counter_open_in_new_window'
  	);

  	foreach ($form_fields as $f) {
  		$useValue = $form_state->getValue($f);
  		if ($f == 'game_start_time' && $useValue !== NULL) {
  			$useValue->setTimezone(new \DateTimeZone('America/Los_Angeles'));
  			$t = intval($useValue->format("U"));
  			error_log('timezone offset [' . $useValue->format("U") . "]: " . $useValue->format("Z"));
  			$useValue = $t - intval($useValue->format("Z"));
  		}
		$this->config('multiplex.settings')
			->set($f, $useValue)
			->save();
    }

    parent::submitForm($form, $form_state);
  }

}
