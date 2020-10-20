<?php

namespace Drupal\multiplex\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

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
      '#default_value' => $this->config('multiplex.settings')->get('inventory_fixed_order') != NULL ? $this->config('multiplex.settings')->get('inventory_fixed_order') : true
    ];
    $form['inventory_links_in_new_window'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Inventory Open Links In New Window'),
      '#description' => $this->t("If an inventory item is usable and is clicked, should a new window open, or should the current window change locations?"),
      '#default_value' => $this->config('multiplex.settings')->get('inventory_links_in_new_window') != NULL ? $this->config('multiplex.settings')->get('inventory_links_in_new_window') : true
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
    $this->config('multiplex.settings')
      ->set('cookie', $form_state->getValue('cookie'))
      ->save();
    $this->config('multiplex.settings')
      ->set('unidentified_user_path', $form_state->getValue('unidentified_user_path'))
      ->save();
    $this->config('multiplex.settings')
      ->set('inventory_cookie', $form_state->getValue('inventory_cookie'))
      ->save();
    $this->config('multiplex.settings')
      ->set('inventory_added_cookie', $form_state->getValue('inventory_added_cookie'))
      ->save();
    $this->config('multiplex.settings')
      ->set('inventory_fixed_order', $form_state->getValue('inventory_fixed_order'))
      ->save();
    $this->config('multiplex.settings')
      ->set('inventory_links_in_new_window', $form_state->getValue('inventory_links_in_new_window'))
      ->save();
    $this->config('multiplex.settings')
      ->set('inventory_wiggle_duration', $form_state->getValue('inventory_wiggle_duration'))
      ->save();
    $this->config('multiplex.settings')
      ->set('inventory_icon_width', $form_state->getValue('inventory_icon_width'))
      ->save();
    $this->config('multiplex.settings')
      ->set('inventory_icon_height', $form_state->getValue('inventory_icon_height'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
